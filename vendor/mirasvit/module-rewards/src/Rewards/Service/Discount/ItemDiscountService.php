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



namespace Mirasvit\Rewards\Service\Discount;

use Magento\Framework\Module\Manager;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\SalesRule\Model\Validator;
use Magento\Tax\Helper\Data as TaxDataHelper;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\Tax\Model\TaxCalculation;
use Mirasvit\Rewards\Helper\Balance\Spend as SpendHelper;
use Mirasvit\Rewards\Helper\Data as DataHelper;
use Mirasvit\Rewards\Helper\Purchase as PurchaseHelper;
use Mirasvit\Rewards\Model\Config;
use Mirasvit\Rewards\Service\Quote\Item\CalcPriceService;
use Mirasvit\RewardsAdminUi\Model\System\Config\Source\Spend\Method;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemDiscountService
{

    private $itemIndex       = 0;

    private $needValidation  = true;

    private $pointsInfo      = [];

    private $quoteItemsPrice = [];

    /**
     * @var AbstractItem
     */
    private $item;

    private $calcPriceService;

    private $config;

    private $itemAddress;

    private $moduleManager;

    private $rewardsData;

    private $rewardsPurchase;

    private $spendHelper;

    private $taxCalculation;

    private $taxConfig;

    private $taxData;

    public function __construct(
        SpendHelper $spendHelper,
        PurchaseHelper $rewardsPurchase,
        DataHelper $rewardsData,
        Config $config,
        CalcPriceService $calcPriceService,
        Manager $moduleManager,
        TaxCalculation $taxCalculation,
        TaxConfig $taxConfig,
        TaxDataHelper $taxData
    ) {
        $this->spendHelper      = $spendHelper;
        $this->rewardsPurchase  = $rewardsPurchase;
        $this->rewardsData      = $rewardsData;
        $this->config           = $config;
        $this->calcPriceService = $calcPriceService;
        $this->moduleManager    = $moduleManager;
        $this->taxCalculation   = $taxCalculation;
        $this->taxConfig        = $taxConfig;
        $this->taxData          = $taxData;
    }

    /**
     * @param AbstractItem $item
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function applyDiscount(AbstractItem $item)
    {
        \Magento\Framework\Profiler::start(__CLASS__ . ':' . __METHOD__);

        if ($this->config->getAdvancedSpendingCalculationMethod() == Method::METHOD_ITEMS &&
            $this->config->getDisableRewardsCalculation()
        ) {
            $this->item = $item;
            // revalidate items
            if (empty(SpendHelper::$itemPoints) && $this->needValidation) {
                $this->spendHelper->getCartRange($this->item->getQuote());
                $this->needValidation = false;
            }

            $items = $this->getAddressItems();
            if (empty($this->quoteItemsPrice[$this->item->getId()])) {
                $purchase = $this->rewardsPurchase->getByQuote($this->item->getQuote());

                $this->quoteItemsPrice = $this->calcPriceService->getQuotePrices($items, $purchase);
            }

            $this->process();
        }
        $this->config->setDisableRewardsCalculation(false);

        \Magento\Framework\Profiler::stop(__CLASS__ . ':' . __METHOD__);
    }

    /**
     * @param Address $address
     *
     * @return Validator
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function applyDiscountDescription(Address $address)
    {
        \Magento\Framework\Profiler::start(__CLASS__ . ':' . __METHOD__);

        $descriptions = (array)$address->getDiscountDescriptionArray();

        $quote    = $address->getQuote();
        $purchase = $this->rewardsPurchase->getByQuote($quote);

        if ($purchase && $purchase->getSpendAmount() > 0) {
            $descriptions[] = $this->rewardsData->getPointsName();
        }

        $address->setDiscountDescriptionArray($descriptions);

        \Magento\Framework\Profiler::stop(__CLASS__ . ':' . __METHOD__);
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function process()
    {
        $this->itemIndex++;

        if (!$this->canProcess() || !in_array($this->item->getId(), SpendHelper::$itemPoints)) {
            return $this;
        }

        $rewardsPrices = $this->quoteItemsPrice[$this->item->getId()];
        $this->item->setRewardsTotalPrice($rewardsPrices['price']);
        $this->item->setRewardsBaseTotalPrice($rewardsPrices['basePrice']);

        if ($this->item->getRewardsTotalPrice() == 0) {
            return $this;
        }

        $this->calcPoints();

        if ($this->pointsInfo['total'] == 0) {//protection from division on zero
            $this->pointsInfo['total'] = $this->item->getRewardsTotalPrice();
        }

        if ($this->pointsInfo['baseTotal'] == 0) {//protection from division on zero
            $this->pointsInfo['baseTotal'] = $this->item->getRewardsBaseTotalPrice();
        }

        $discount     = $this->item->getRewardsTotalPrice() / $this->pointsInfo['total'] * $this->pointsInfo['spendAmount'];
        $baseDiscount = $this->item->getRewardsBaseTotalPrice() / $this->pointsInfo['baseTotal'] *
            $this->pointsInfo['baseSpendAmount'];

        $rate = $this->getPercentTax();
        if ($rate) {
            $delta        = $this->item->getRewardsTotalPrice() - $discount;
            $baseDelta    = $this->item->getRewardsBaseTotalPrice() - $baseDiscount;
            $discount     = $this->item->getData(OrderItemInterface::ROW_TOTAL) - ($delta / (1 + $rate));
            $baseDiscount = $this->item->getData(OrderItemInterface::BASE_ROW_TOTAL) - ($baseDelta / (1 + $rate));
        }

        if ($discount > $this->item->getRewardsTotalPrice()) {
            $discount = $this->item->getRewardsTotalPrice();
        }

        if ($baseDiscount > $this->item->getRewardsBaseTotalPrice()) {
            $baseDiscount = $this->item->getRewardsBaseTotalPrice();
        }

        $itemsRewardsDiscount     = $this->item->getQuote()->getItemsRewardsDiscount();
        $baseItemsRewardsDiscount = $this->item->getQuote()->getBaseItemsRewardsDiscount();

        $this->item->getQuote()->setItemsRewardsDiscount($itemsRewardsDiscount + $discount);
        $this->item->getQuote()->setBaseItemsRewardsDiscount($baseItemsRewardsDiscount + $baseDiscount);

        $discount     += $this->item->getDiscountAmount();
        $baseDiscount += $this->item->getBaseDiscountAmount();

        $discount = $this->roundPriceWithFaonniPrice($discount);

        $this->item->setDiscountAmount($discount);
        $this->item->setBaseDiscountAmount($baseDiscount);

        if ($this->item->getQuote()->getItemsCount() == $this->itemIndex) {
            $this->quoteItemsPrice = [];
        }

        return $this;
    }

    /**
     * @param float $price
     *
     * @return float
     */
    private function roundPriceWithFaonniPrice($price)
    {
        if (!$this->moduleManager->isEnabled('Faonni_Price')) {
            return $price;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper        = $objectManager->create('Faonni\Price\Helper\Data');
        $math          = $objectManager->create('Faonni\Price\Model\Math');

        if (!$helper->isEnabled() ||
            !$helper->isRoundingDiscount()
        ) {
            return $price;
        }

        return $math->round($price);
    }

    /**
     * @return void
     */
    private function calcPoints()
    {
        $quoteItems   = $this->getAddressItems();
        $rewardsTotal = $rewardsBaseTotal = 0;

        foreach ($quoteItems as $quoteItem) {
            if (in_array($quoteItem->getId(), SpendHelper::$itemPoints) &&
                isset($this->quoteItemsPrice[$quoteItem->getId()])
            ) {
                $rewardsPrices    = $this->quoteItemsPrice[$quoteItem->getId()];
                $rewardsTotal     += $rewardsPrices['price'];
                $rewardsBaseTotal += $rewardsPrices['basePrice'];
            }
        }

        $total     = $rewardsTotal;
        $baseTotal = $rewardsBaseTotal;

        $purchase        = $this->rewardsPurchase->getByQuote($this->item->getQuote());
        $baseSpendAmount = $purchase->getSpendAmount();

        if (!$baseTotal) {
            $baseTotal = $total;
        }

        if ($baseSpendAmount > $baseTotal) {
            $baseSpendAmount = $baseTotal;
        }

        $currencyRate = 1;
        if ($baseTotal > 0 && !$this->isCustomRoundingEnabled()) { //for some reason subtotal can be 0
            $currencyRate = $total / $baseTotal;
        }

        $spendAmount = round($baseSpendAmount * $currencyRate, 2, PHP_ROUND_HALF_DOWN);

        $this->pointsInfo = [
            'tax'              => $this->itemAddress->getTaxAmount(),
            'total'            => $total,
            'baseTotal'        => $baseTotal,
            'spendAmount'      => $spendAmount,
            'baseSpendAmount'  => $baseSpendAmount,
            'totalSpendAmount' => $purchase->getSpendAmount(),
            'currencyRate'     => $currencyRate,
        ];
    }


    /**
     * We need this because Faonni_Price changes total without basetotal
     * @return bool
     */
    private function isCustomRoundingEnabled()
    {
        $address = $this->itemAddress;

        return $this->moduleManager->isEnabled('Faonni_Price') &&
            $address->getQuote()->getBaseCurrencyCode() == $address->getQuote()->getQuoteCurrencyCode();
    }

    /**
     * @return float
     */
    private function getPercentTax()
    {
        $rate = $this->taxCalculation->getCalculatedRate(
            $this->item->getData('tax_class_id'),
            $this->item->getQuote()->getCustomerId(),
            $this->item->getQuote()->getStoreId()
        );

        return $this->taxConfig->applyTaxAfterDiscount() && !$this->taxConfig->priceIncludesTax()
        && $this->config->getGeneralIsIncludeTaxSpending() ? ($rate / 100) : 0;
    }

    /**
     * @return bool
     */
    private function canProcess()
    {
        $quote = $this->item->getQuote();

        if ($quote->getIsMultiShipping()) {
            return false;
        }

        if (!$quote->getId()) {
            return false;
        }

        $purchase = $this->rewardsPurchase->getByQuote($quote);

        if (!$purchase->getSpendAmount()) {
            return false;
        }

        $spendAmount = $purchase->getSpendAmount();
        if ($spendAmount == 0) {
            return false;
        }

        if (empty($this->quoteItemsPrice[$this->item->getId()])) {
            return false;
        }

        return true;
    }

    /**
     * @return void
     */
    private function initItemAddress()
    {
        if ($this->itemAddress) {
            return;
        }

        $item              = $this->item;
        $this->itemAddress = $item->getAddress();

        if (!count($this->itemAddress->getAllItems()) &&
            $item->getAddress()->getAddressType() == Address::ADDRESS_TYPE_SHIPPING
        ) {
            $this->itemAddress = $item->getQuote()->getBillingAddress();
        }
    }

    /**
     * @return AbstractItem[]
     */
    private function getAddressItems()
    {
        $this->initItemAddress();
        $items = $this->itemAddress->getAllItems();

        return $items;
    }
}
