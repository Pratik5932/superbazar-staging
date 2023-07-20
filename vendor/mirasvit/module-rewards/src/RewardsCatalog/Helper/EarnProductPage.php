<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rewards
 * @version   3.0.57
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\RewardsCatalog\Helper;

use Magento\Framework\Exception\LocalizedException;
use Mirasvit\Rewards\Api\Data\Earning\RuleInterface;
use Mirasvit\Rewards\Api\Data\TierInterface;
use Mirasvit\Rewards\Model\Config as Config;
use \Mirasvit\Rewards\Model\Earning\Rule;
use \Magento\Catalog\Model\Product;
use \Magento\Customer\Model\Customer;

/**
 * Calculate earning points for product page
 */
class EarnProductPage
{
    /**
     * @var array
     */
    private $productMessages = [];
    /**
     * @var \Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\CollectionFactory
     */
    private $earningRuleCollectionFactory;
    /**
     * @var \Mirasvit\Rewards\Helper\Balance\Earn
     */
    private $earnHelper;

    public function __construct(
        \Mirasvit\Rewards\Helper\Balance\Earn $earnHelper,
        \Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\CollectionFactory $earningRuleCollectionFactory
    ) {
        $this->earningRuleCollectionFactory = $earningRuleCollectionFactory;
        $this->earnHelper = $earnHelper;
    }




    /**
     * @param Product $product
     * @param float $price
     * @param Customer $customer
     * @param int $websiteId
     * @return float
     */
    public function getProductPagePoints(Product $product, $price, Customer $customer, $websiteId)
    {
        \Magento\Framework\Profiler::start(__METHOD__);
        $totalPoints = $this->getCartProductPoints($product, $price, $customer, $websiteId);
        $points = $this->earnHelper->roundPoints($totalPoints);
        \Magento\Framework\Profiler::stop(__METHOD__);

        return $points;
    }

    /**
     * @param Product $product
     * @param float $price
     * @param Customer $customer
     * @param int $websiteId
     * @return int
     */
    private function getCartProductPoints(Product $product, $price, Customer $customer, $websiteId)
    {
        \Magento\Framework\Profiler::start(__METHOD__);
        $total = 0;

        $rules = $this->earningRuleCollectionFactory->create()
            ->addWebsiteFilter($websiteId)
            // we can not use getGroupId(), because Magento set wrong group for guest customer
            ->addCustomerGroupFilter((int)$customer->getData('group_id'))
            ->addCurrentFilter()
            ->addShowOnProductPageFilter();
        $rules->getSelect()->order('sort_order');

        // to transfer customer object to the rules and validate customer conditions
        $product->setCustomer($customer);

        /** @var \Mirasvit\Rewards\Model\Earning\Rule $rule */
        foreach ($rules as $rule) {
            $rule->afterLoad();
            $rule->setIsProductPage(true);
            $validCondition = $rule->validate($product);
            $validAction    = $rule->getActions()->validate($product);
            if ($validCondition && $validAction) {
                $total += $this->getPointsPerRule($rule, $customer, $price, $product->getRewardsQty());
                if ($rule->getProductNotification()) {
                    $this->addProductMessage($product->getId(), $rule->getId(), $rule->getProductNotification());
                }
                if ($rule->getIsStopProcessing()) {
                    break;
                }
            }
        }
        \Magento\Framework\Profiler::stop(__METHOD__);

        return $total;
    }

    /**
     * @param Rule     $rule
     * @param Customer $customer
     * @param float    $price
     * @param int      $qty
     * @return int
     */
    private function getPointsPerRule(Rule $rule, Customer $customer, $price, $qty = 1)
    {
        $tier = $rule->getTier($customer);
        switch ($tier->getEarningStyle()) {
            case Config::EARNING_STYLE_GIVE:
                return $tier->getEarnPoints();
            case Config::EARNING_STYLE_AMOUNT_SPENT:
                $step = $this->earnHelper->roundPoints($price / $tier->getMonetaryStep());
                $amount = $step * $tier->getEarnPoints();
                if (
                    $tier->getPointsLimit() &&
                    $amount > $tier->getPointsLimit()
                ) {
                    $amount = $tier->getPointsLimit();
                }
                return $amount;
            case Config::EARNING_STYLE_QTY_SPENT:
                $qty = round($qty/$tier->getQtyStep(), 0, PHP_ROUND_HALF_DOWN);
                $amount = $qty * $tier->getEarnPoints();
                if ($tier->getPointsLimit() && $amount > $tier->getPointsLimit()) {
                    $amount = $tier->getPointsLimit();
                }
                return $amount;
        }
        return 0;
    }

    /**
     * @param int $productId
     * @param int $ruleId
     * @param string $message
     */
    public function addProductMessage($productId, $ruleId, $message)
    {
        $this->productMessages[$productId][$ruleId] = $message;
    }


    /**
     * @param int $productId
     * @return array
     */
    public function getProductMessages($productId)
    {
        return isset($this->productMessages[$productId]) ? $this->productMessages[$productId] : [];
    }

}