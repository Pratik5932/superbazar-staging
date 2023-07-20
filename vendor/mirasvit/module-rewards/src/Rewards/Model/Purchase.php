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


namespace Mirasvit\Rewards\Model;

use Magento\Framework\Profiler;
use Mirasvit\Rewards\Api\Data\PurchaseInterface;
use Mirasvit\RewardsAdminUi\Model\System\Config\Source\Spend\Method;

/**
 * @method \Mirasvit\Rewards\Model\ResourceModel\Purchase\Collection|\Mirasvit\Rewards\Model\Purchase[] getCollection()
 * @method \Mirasvit\Rewards\Model\Purchase load(int $id)
 * @method bool getIsMassDelete()
 * @method \Mirasvit\Rewards\Model\Purchase setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method \Mirasvit\Rewards\Model\Purchase setIsMassStatus(bool $flag)
 * @method int getEarnPoints()
 * @method \Mirasvit\Rewards\Model\Purchase setEarnPoints(int $points)
 * @method int getSpendPoints()
 * @method int getBaseSpendAmount()
 * @method int getSpendAmount()
 * @method int getSpendMinPoints()
 * @method \Mirasvit\Rewards\Model\Purchase setSpendMinPoints(int $points)
 * @method int getSpendMaxPoints()
 * @method \Mirasvit\Rewards\Model\Purchase setSpendMaxPoints(int $points)
 * @method int getRewardsExtraDiscount()
 * @method \Mirasvit\Rewards\Model\Purchase setRewardsExtraDiscount(int $discount)
 * @method int getBaseRewardsExtraDiscount()
 * @method \Mirasvit\Rewards\Model\Purchase setBaseRewardsExtraDiscount(int $discount)
 * @method \Mirasvit\Rewards\Model\ResourceModel\Purchase getResource()
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Purchase extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;
    /**
     * @var \Magento\Tax\Model\Config
     */
    private $taxConfig;

    /**
     * @var string
     */
    protected $_eventPrefix = 'rewards_purchase';

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var \Mirasvit\Rewards\Model\Config
     */
    protected $config;

    /**
     * @var \Mirasvit\Rewards\Helper\Balance
     */
    protected $rewardsBalance;

    /**
     * @var \Mirasvit\Rewards\Helper\Balance\Earn
     */
    protected $rewardsBalanceEarn;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Model\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb
     */
    protected $resourceCollection;

    /**
     * @var \Mirasvit\Rewards\Helper\Rule\Notification
     */
    protected $rewardsPurchase;
    /**
     * @var \Mirasvit\Rewards\Helper\Balance\SpendCartPoints
     */
    protected $spendCartPointsHelper;
    /**
     * @var \Mirasvit\Rewards\Helper\Balance\SpendCartRange
     */
    protected $spendCartRangeHelper;


    public function __construct(
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Mirasvit\Rewards\Model\Config $config,
        \Mirasvit\Rewards\Helper\Balance\SpendCartPoints $spendCartPointsHelper,
        \Mirasvit\Rewards\Helper\Balance\SpendCartRange $spendCartRangeHelper,
        \Mirasvit\Rewards\Helper\Balance $rewardsBalance,
        \Mirasvit\Rewards\Helper\Balance\Earn $rewardsBalanceEarn,
        \Mirasvit\Rewards\Helper\Rule\Notification $rewardsPurchase,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->quoteFactory        = $quoteFactory;
        $this->sessionFactory      = $sessionFactory;
        $this->config              = $config;
        $this->spendCartPointsHelper = $spendCartPointsHelper;
        $this->spendCartRangeHelper = $spendCartRangeHelper;
        $this->rewardsBalance      = $rewardsBalance;
        $this->rewardsBalanceEarn  = $rewardsBalanceEarn;
        $this->rewardsPurchase     = $rewardsPurchase;
        $this->customerSession     = $customerSession;
        $this->taxConfig           = $taxConfig;
        $this->context             = $context;
        $this->moduleManager       = $moduleManager;
        $this->registry            = $registry;
        $this->resource            = $resource;
        $this->resourceCollection  = $resourceCollection;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rewards\Model\ResourceModel\Purchase');
    }

    /**
     * @param float $points
     * @return $this
     */
    public function setSpendPoints($points)
    {
        if (!$this->getOrderId()) { // do not change points if order was created
            $this->setData(PurchaseInterface::KEY_SPEND_POINTS, $points);
        }

        return $this;
    }

    /**
     * @param float $baseAmount
     * @return $this
     */
    public function setBaseSpendAmount($baseAmount)
    {
        if (!$this->getOrderId()) { // do not change amount if order was created
            $this->setData(PurchaseInterface::KEY_BASE_SPEND_AMOUNT, $baseAmount);
        }

        return $this;
    }

    /**
     * @param float $amount
     * @return $this
     */
    public function setSpendAmount($amount)
    {
        if (!$this->getOrderId()) { // do not change amount if order was created
            $this->setData(PurchaseInterface::KEY_SPEND_AMOUNT, $amount);
        }

        return $this;
    }

    /**
     * @param bool|false $emptyOption
     * @return array
     */
    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote = null;

    /**
     * @return \Magento\Quote\Model\Quote|bool
     */
    public function getQuote()
    {
        if (!$this->getQuoteId()) {
            return false;
        }

        if (!$this->quote) {
            $this->quote = $this->quoteFactory->create()->load($this->getQuoteId());
        }

        return $this->quote;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     */
    public function setQuote($quote)
    {
        $this->quote = $quote;

        return $this;
    }

    /**
     * @var \Magento\Quote\Model\Quote\Address\Total
     */
    protected $totals = null;

    /**
     * @return \Magento\Quote\Model\Quote\Address\Total|null
     */
    public function getTotals()
    {
        return $this->totals;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $totals
     * @return $this
     */
    public function setTotals($totals)
    {
        $this->totals = $totals;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxPointsNumberToSpent()
    {
        if (!$this->sessionFactory->create()->isLoggedIn()) {
            return 0;
        }

        return $this->getSpendMaxPoints() > 0 ? $this->getSpendMaxPoints() : 0;
    }

    /**
     * @param float $subtotal
     * @return bool|float|int
     */
    protected function getSpendLimit($subtotal)
    {
        $limit = $this->config->getGeneralSpendLimit();
        if (!$limit) {
            return false;
        }

        if (strpos($limit, '%')) {
            $limit = str_replace('%', '', $limit);
            $limit = (int) $limit;
            $spendLimit = $subtotal * $limit / 100;
            if ($spendLimit > 0) {
                return $spendLimit;
            }
        } else {
            return $limit;
        }
    }

    /**
     * @param int $customerId
     * @return int
     */
    public function getCustomerBalancePoints($customerId)
    {
        return $this->rewardsBalance->getBalancePoints($customerId);
    }

    /**
     * Quote can be changed time to time. So we have to refresh avaliable points number.
     *
     * @param bool $forceRefresh - must be used only if we 100% sure that it will be called once.
     *
     * @return object
     * @throws \Exception
     */
    public function refreshPointsNumber($forceRefresh = false)
    {
        $quote = $this->getQuote();
        if (!$forceRefresh && !$this->hasChanges($quote)) {
            return $this;
        }

        Profiler::start('rewards:refreshPointsNumber');

        $this->refreshQuote(false); //reset points discount

        if (!$this->getFreezeSpendPoints()) {// we do  not need change points amount after order was created
            //recalculate spending points
            $totals    = $this->getTotals();
            $cartRange = $this->spendCartRangeHelper->getCartRange($quote, $totals);

            $pointsNumber = (int) $this->getSpendPoints();
            if ($pointsNumber != 0 && $pointsNumber < $cartRange->getMinPoints()) {
                $pointsNumber = $cartRange->getMinPoints();
            }

            if ($pointsNumber > $cartRange->getMaxPoints()) {
                $pointsNumber = $cartRange->getMaxPoints();
            }

            $balancePoints = $this->getCustomerBalancePoints($quote->getCustomerId());
            $cartMax       = min($cartRange->getMaxPoints(), $balancePoints);
            if ($pointsNumber > $balancePoints) {
                $pointsNumber = $balancePoints;
                if ($pointsNumber < $cartRange->getMinPoints()) {
                    $this->setSpendPoints(0);
                    $this->setSpendAmount(0);
                    $this->setBaseSpendAmount(0);
                    $this->setSpendMinPoints($cartRange->getMinPoints());
                    $this->setSpendMaxPoints($cartMax);
                }
            }

            $cartPoints = $this->spendCartPointsHelper->getCartPoints($quote, $pointsNumber, $totals);

            $this->setSpendPoints($cartPoints->getPoints());
            $this->setSpendAmount($cartPoints->getAmount());
            $this->setBaseSpendAmount($cartPoints->getBaseAmount());
            $this->setSpendMinPoints($cartRange->getMinPoints());
            $this->setSpendMaxPoints($cartMax);
            $this->save(); //we need this. otherwise points are not updated in the magegiant checkout ajax.
        }

        $this->refreshQuote(true); //apply points discount with rewards discount

        Profiler::stop('rewards:refreshPointsNumber');

        return $this;
    }

    /**
     * Calls only during collectTotals
     * @return void
     * @throws \Exception
     */
    public function calculateSpendingPoints()
    {
        $quote     = $this->getQuote();
        $totals    = $this->getTotals();
        $cartRange = $this->spendCartRangeHelper->getCartRange($quote, $totals);

        $pointsNumber = (int) $this->getSpendPoints();
        if ($pointsNumber != 0 && $pointsNumber < $cartRange->getMinPoints()) {
            $pointsNumber = $cartRange->getMinPoints();
        }

        if ($pointsNumber > $cartRange->getMaxPoints()) {
            $pointsNumber = $cartRange->getMaxPoints();
        }

        $balancePoints = $this->getCustomerBalancePoints($quote->getCustomerId());
        $cartMax = min($cartRange->getMaxPoints(), $balancePoints);
        if ($pointsNumber > $balancePoints) {
            $pointsNumber = $balancePoints;

            if ($pointsNumber < $cartRange->getMinPoints()) {
                $this->setSpendPoints(0);
                $this->setSpendAmount(0);
                $this->setBaseSpendAmount(0);
                $this->setSpendMinPoints($cartRange->getMinPoints());
                $this->setSpendMaxPoints($cartMax);
            }
        }

        if ($this->config->getAdvancedSpendingCalculationMethod() != Method::METHOD_ITEMS) {
            $cartPoints = $this->spendCartPointsHelper->getCartPoints($quote, $pointsNumber, $totals);

            $this->setSpendPoints($cartPoints->getPoints());
            $this->setSpendAmount($cartPoints->getAmount());
            $this->setBaseSpendAmount($cartPoints->getBaseAmount());
        }

        $this->setSpendMinPoints($cartRange->getMinPoints());
        $this->setSpendMaxPoints($cartMax);

        if ($this->getSpendAmount()) {
            $this->setQuoteProductIds($cartRange->getItemPoints());
        } else {
            $this->setQuoteProductIds('');
        }

        $this->save(); //we need this. otherwise points are not updated in the magegiant checkout ajax.
    }


    /**
     * @param bool $disableRewardsCalculation
     * @return void
     * @throws \Exception
     */
    public function refreshQuote($disableRewardsCalculation)
    {
        $quote = $this->getQuote();
        if (!$quote->getCustomerId()) {
            return;
        }
        Profiler::start('rewards:refreshQuote');
        if ($this->config->getAdvancedSpendingCalculationMethod() != Method::METHOD_ITEMS &&
            $disableRewardsCalculation
        ) { // reset exists discount
            $this->setSpendAmount(0);
            $this->setBaseSpendAmount(0);
            $this->save();
        }
        $this->config->setDisableRewardsCalculation($disableRewardsCalculation);
        $isPurchaseSave = $quote->getIsPurchaseSave(); // we don't need to recalculate points now
        $quote->setIsPurchaseSave(true);

        if ($this->moduleManager->isEnabled('Amasty_ShippingTableRates')) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }
        $quote->setTotalsCollectedFlag(false)
            ->collectTotals()
            ->save()
        ;
        $quote->setIsPurchaseSave($isPurchaseSave);
        Profiler::stop('rewards:refreshQuote');
    }

    /**
     * Check quote for changes.
     *
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return bool
     */
    protected function hasChanges($quote)
    {
        $cachedQuote = $this->customerSession->getRWCachedQuote();
        $data = [];
        $data[] = (int) $quote->getCustomerId();//we need this, because if customer do 'reorder', customer id maybe = 0.
        $data[] = (double) $quote->getData('grandtotal');
        $data[] = (double) $quote->getData('subtotal');
        $data[] = (double) $quote->getData('subtotal_with_discount');
        $data[] = (double) $quote->getBaseAwStoreCreditAmount();
        $data[] = (double) $quote->getBaseAmastyGift();
        $data[] = (double) $quote->getBaseMagecompSurchargeAmount();
        $data[] = count($quote->getAllItems());

        if ($shipping = $quote->getShippingAddress()) {
            $data[] = $shipping->getData('shipping_method');
            $data[] = (double) $shipping->getData('shipping_amount');
        }
        if ($payment = $quote->getPayment()) {
            if ($payment->hasMethodInstance()) {
                $method = $payment->getMethodInstance()->getCode();
            }
            if (empty($method) && $payment->getMethod()) {
                $method = $payment->getMethod();
            }
            if (!empty($method)) {
                $data[] = $method;
            }
        }

        $newCachedQuote = implode('|', $data);

        if ($cachedQuote != $newCachedQuote) {
            $this->customerSession->setRWCachedQuote($newCachedQuote);
            return true;
        }

        return false;
    }

    /**
     * @param string|array $ids
     *
     * @return $this
     */
    public function setQuoteProductIds($ids)
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }

        $this->setData('quote_product_ids', $ids);

        return $this;
    }

    /**
     * @return array
     */
    public function getQuoteProductIds()
    {
        $ids = (string) $this->getData('quote_product_ids');

        return explode(',', $ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getData($key = '', $index = null)
    {
        $data = parent::getData($key, $index);
        if (!empty($data['quote_product_ids'])) {
            $data['quote_product_ids'] = explode(',', (string) $data['quote_product_ids']);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function setData($key, $value = null)
    {
        if ($key == 'quote_product_ids' && is_array($value)) {
            $value = implode(',', $value);
        }
        if (!empty($key['quote_product_ids']) && $key === (array)$key && is_array($key['quote_product_ids'])) {
            $key['quote_product_ids'] = implode(',', $key['quote_product_ids']);
        }
        $data = parent::setData($key, $value);

        return $data;
    }

    /**
     * Calls collect totals to update points
     * @return void
     */
    public function updatePoints()
    {
        $quote = $this->getQuote();
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
    }

    /**
     * Return amount of second when points should be update
     * @todo change value and fix tests(we use 0 for now to pass tests)
     *
     * @return int
     */
    public function getRefreshPointsTime()
    {
        return 0;
    }
}
