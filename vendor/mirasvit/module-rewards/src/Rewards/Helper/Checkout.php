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



namespace Mirasvit\Rewards\Helper;

use Mirasvit\Rewards\Model\Config\Source\Spending\ApplyTax;
use Mirasvit\RewardsAdminUi\Model\System\Config\Source\Spend\Method;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Checkout extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $cart;

    protected $cartRepository;

    protected $quoteFactory;

    protected $rewardsData;

    protected $rewardsPurchase;

    private   $rewardsConfig;

    protected $taxConfig;

    protected $context;

    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Tax\Model\Config $taxConfig,
        \Mirasvit\Rewards\Helper\Purchase $rewardsPurchase,
        \Mirasvit\Rewards\Helper\Data $rewardsData,
        \Mirasvit\Rewards\Model\Config $rewardsConfig,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->cart            = $cart;
        $this->quoteFactory    = $quoteFactory;
        $this->cartRepository  = $cartRepository;
        $this->rewardsPurchase = $rewardsPurchase;
        $this->rewardsData     = $rewardsData;
        $this->rewardsConfig   = $rewardsConfig;
        $this->taxConfig       = $taxConfig;
        $this->context         = $context;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Checkout\Model\Cart
     */
    protected function _getCart()
    {
        return $this->cart;
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->context->getRequest();
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return array
     */
    public function processAdminRequest($quote)
    {
        $purchase = $this->rewardsPurchase->getByQuote($quote);

        return $this->process($purchase);
    }

    /**
     * @param string $paymentMethod
     *
     * @return array
     */
    public function updatePaymentMethod($paymentMethod)
    {
        $response = [
            'success' => false,
            'points'  => false,
        ];

        /** $var \Mirasvit\Rewards\Model\Purchase $purchase */
        if (($purchase = $this->rewardsPurchase->getPurchase()) && $purchase->getQuote()->getCustomerId()) {
            $quote = $purchase->getQuote();

            if ($quote->getItemVirtualQty() > 0) {
                $quote->getBillingAddress()->setPaymentMethod($paymentMethod);
            } else {
                $quote->getShippingAddress()->setPaymentMethod($paymentMethod);
            }

            if ($purchase->getQuote()->getShippingAddress()->getCountryId() !== null) {
                $shippingCarrier = $this->getRequest()->getParam('shipping_carrier');
                $shippingMethod  = $this->getRequest()->getParam('shipping_method');
                $purchase->getQuote()->setCartShippingCarrier($shippingCarrier);
                $purchase->getQuote()->setCartShippingMethod($shippingMethod);
            }

            if ($this->rewardsConfig->getAdvancedSpendingCalculationMethod() == Method::METHOD_ITEMS) {
                $purchase->refreshPointsNumber(true);
                $quote->setIncludeSurcharge(false);
            }

            $response['success'] = (bool)$purchase->getEarnPoints();
            $response['points']  = $this->rewardsData->formatPoints($purchase->getEarnPoints());

            $response['chechoutRewardsPointsSpend'] = 0;
            $response['chechoutRewardsPointsUsed']  = 0;

            if ($point = $purchase->getSpendPoints()) {
                $response['chechoutRewardsPointsSpend'] = $this->rewardsData->formatPoints($point);
                $response['chechoutRewardsPointsUsed']  = $point;
            }

            $quote = $purchase->getQuote();

            $response['chechoutRewardsPointsAvailble'] = $this->rewardsData->formatPoints(
                $purchase->getCustomerBalancePoints($quote->getCustomerId())
            );
            $response['chechoutRewardsPointsMax']      = $purchase->getMaxPointsNumberToSpent();
            $response['chechoutRewardsIsShow']         = (bool)$response['chechoutRewardsPointsMax'];

            $response['rewardsLogo'] = $this->rewardsData->getLogoHtml();
        }

        return $response;
    }

    /**
     * @param \Mirasvit\Rewards\Model\Purchase $purchase
     *
     * @return array
     */
    public function processApiRequest($purchase)
    {
        if (!$purchase->getQuote()->getItemsCount()) {
            return [
                'success' => false,
                'message' => false,
            ];
        }

        $result = $this->process($purchase);
        if (is_object($result['message'])) {
            $result['message'] = $result['message']->render();
        }

        return $result;
    }

    /**
     * @return array
     */
    public function processRequest()
    {
        /*
         * No reason continue with empty shopping cart
         */
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            return [
                'success' => false,
                'message' => false,
            ];
        }

        $purchase = $this->rewardsPurchase->getPurchase();
        if (!$purchase->getQuote()->getIsVirtual() &&
            $this->getRequest()->getParam('shipping_carrier') &&
            empty(trim((string) $purchase->getQuote()->getShippingAddress()->getShippingMethod(), '_'))
        ) {
            if ($purchase->getQuote()->getShippingAddress()->getCountryId() === null) {
                $addressData = (array)json_decode($this->getRequest()->getParam('address'), true);
                $convertData = [
                    'customerAddressId' => 'customer_address_id',
                    'countryId'         => 'country_id',
                    'regionId'          => 'region_id',
                    'regionCode'        => 'region_code',
                    'customerId'        => 'customer_id',
                ];

                foreach ($addressData as $k => $v) {
                    if (isset($convertData[$k])) {
                        $purchase->getQuote()->getShippingAddress()->setData($convertData[$k], $v);
                    } else {
                        $purchase->getQuote()->getShippingAddress()->setData($k, $v);
                    }
                }
            }

            $shippingCarrier = $this->getRequest()->getParam('shipping_carrier');
            $shippingMethod  = $this->getRequest()->getParam('shipping_method');
            $purchase->getQuote()->setCartShippingCarrier($shippingCarrier);
            $purchase->getQuote()->setCartShippingMethod($shippingMethod);
            $purchase->getQuote()->getShippingAddress()
                ->setCollectShippingRates(true)
                ->setShippingMethod(
                    $shippingCarrier . '_' . $shippingMethod
                );
        }


        return $this->process($purchase);
    }

    /**
     * @param \Mirasvit\Rewards\Model\Purchase $purchase
     *
     * @return array
     */
    private function process($purchase)
    {
        $response     = [
            'success' => false,
            'message' => false,
        ];

        $pointsNumber = abs((int)$this->getRequest()->getParam('points_amount'));
        if ($this->getRequest()->getParam('remove-points') == 1) {
            $pointsNumber = 0;
        }

        $oldPointsNumber = $purchase->getSpendPoints();

        if ($pointsNumber <= 0 && $oldPointsNumber <= 0) {
            return $response;
        }

        try {
            $this->updatePurchase($purchase, $pointsNumber);

            //we should update purchase to show correct points number in cart and checkout rewards message
            $newPurchase = $this->rewardsPurchase->getPurchase();
            if ($newPurchase) {
                $purchase = $newPurchase;
            }

            $response = $this->successResponse($purchase, $pointsNumber);
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = __('Cannot apply %1. %2 %3', $this->rewardsData->getPointsName(), $e->getMessage(), $e->getTraceAsString());

            $this->context->getLogger()->error($e->getMessage());
        }
        $response['spend_points']          = $purchase->getSpendPoints();
        $response['spend_points_formated'] = $this->rewardsData->formatPoints($purchase->getSpendPoints());

        return $response;
    }

    /**
     * @param float                            $pointsNumber
     * @param \Mirasvit\Rewards\Model\Purchase $purchase
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Currency_Exception
     */
    private function successResponse($purchase, $pointsNumber)
    {
        $response = [];
        if ($pointsNumber) {
            $response['success'] = true;
            $response['message'] = __(
                '%1 were applied.', $this->rewardsData->formatPoints($purchase->getSpendPoints())
            );

            // do not check max because max will be use instead of $pointsNumber
            if ($pointsNumber != $purchase->getSpendPoints() && $pointsNumber < $purchase->getSpendMinPoints()) {
                $response['success'] = false;
                $response['message'] = __(
                    'Minimum number is %1.', $this->rewardsData->formatPoints($purchase->getSpendMinPoints())
                );
            }
        } else {
            $response['success'] = true;
            $response['message'] = __('%1 were cancelled.', $this->rewardsData->getPointsName());
        }

        return $response;
    }

    /**
     * @param \Mirasvit\Rewards\Model\Purchase $purchase
     * @param float                            $pointsNumber
     */
    public function updatePurchase($purchase, $pointsNumber)
    {
        if ($this->rewardsConfig->getAdvancedSpendingCalculationMethod() == Method::METHOD_ITEMS) {
            $purchase->getQuote()->setIncludeSurcharge(true);
            $purchase->setSpendPoints($pointsNumber)
                ->refreshPointsNumber(true)
                ->save();
            $purchase->getQuote()->setIncludeSurcharge(false);

            return;
        }

        $purchase
            ->setSaveItemIds(true)
            ->setSpendPoints($pointsNumber);

        if (!$pointsNumber) {
            $purchase->setBaseSpendAmount(0)
                ->setSpendAmount(0);
        }

        $purchase->save();

        $quote = $purchase->getQuote();
        if ($this->isApplyTaxAfterDiscount()) {
            $purchase->updatePoints(); // apply rewards discount
            $quote->setTotalsCollectedFlag(false); // recalculate tax with rewards discount
        }

        $this->cartRepository->save($quote->collectTotals());
    }

    /**
     * @return bool
     */
    private function isApplyTaxAfterDiscount()
    {
        return $this->taxConfig->applyTaxAfterDiscount() &&
            $this->rewardsConfig->getGeneralApplyTaxAfterSpendingDiscount() == ApplyTax::APPLY_SPENDING_TAX_DEFAULT;
    }
}
