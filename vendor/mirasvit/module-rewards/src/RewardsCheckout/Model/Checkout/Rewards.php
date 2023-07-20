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



namespace Mirasvit\RewardsCheckout\Model\Checkout;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Mirasvit\Rewards\Helper\Balance;
use Mirasvit\Rewards\Helper\Checkout;
use Mirasvit\Rewards\Helper\Data;
use Mirasvit\Rewards\Helper\Purchase as PurchaseHelper;
use Mirasvit\Rewards\Model\Config;
use Mirasvit\Rewards\Model\Data\RewardsFactory;
use Mirasvit\Rewards\Model\Purchase;
use Mirasvit\RewardsAdminUi\Model\System\Config\Source\Spend\Method;

/**
 * Class TotalsInformationManagement
 */
class Rewards implements \Mirasvit\Rewards\Api\RewardsInterface
{
    private $config;

    private $customerRepository;

    private $request;

    private $quoteRepository;

    private $rewardsDataFactory;

    private $rewardsBalance;

    private $rewardsData;

    private $rewardsPurchase;

    private $rewardsCheckout;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        RequestInterface $request,
        CartRepositoryInterface $quoteRepository,
        RewardsFactory $rewardsDataFactory,
        Config $config,
        Balance $rewardsBalance,
        Checkout $rewardsCheckout,
        Data $rewardsData,
        PurchaseHelper $rewardsPurchase
    ) {
        $this->config             = $config;
        $this->customerRepository = $customerRepository;
        $this->request            = $request;
        $this->quoteRepository    = $quoteRepository;
        $this->rewardsDataFactory = $rewardsDataFactory;
        $this->rewardsBalance     = $rewardsBalance;
        $this->rewardsData        = $rewardsData;
        $this->rewardsPurchase    = $rewardsPurchase;
        $this->rewardsCheckout    = $rewardsCheckout;
    }

    /**
     * {@inheritdoc}
     */
    public function update($shippingCarrier = '', $shippingMethod = '', $paymentMethod = '')
    {
        $result = [
            'chechoutRewardsIsShow'         => 0,
            'chechoutRewardsPoints'         => 0,
            'chechoutRewardsPointsMax'      => 0,
            'chechoutRewardsPointsSpend'    => 0,
            'chechoutRewardsPointsAvailble' => 0,
            'rewardsLogo'                   => $this->rewardsData->getLogoHtml(),
        ];

        if (($purchase = $this->rewardsPurchase->getPurchase()) && $purchase->getQuote()->getCustomerId()) {

            if ($this->config->getAdvancedSpendingCalculationMethod() == Method::METHOD_ITEMS) {

                if ($purchase->getQuote()->getShippingAddress()->getCountryId() !== null) {
                    $purchase->getQuote()->setCartShippingCarrier($shippingCarrier);
                    $purchase->getQuote()->setCartShippingMethod($shippingMethod);
                }

                $purchase->refreshQuote(true);//shipping not available in cart so we need to save it
                $purchase->refreshPointsNumber(true);
            } else {
                $this->refreshPoints($purchase, $shippingCarrier, $shippingMethod, $paymentMethod);
            }

            if ($purchase->getEarnPoints()) {
                $result['chechoutRewardsPoints'] = $this->rewardsData->formatPoints($purchase->getEarnPoints());
            }

            if ($point = $purchase->getSpendPoints()) {
                $result['chechoutRewardsPointsSpend'] = $this->rewardsData->formatPoints($point);
                $result['chechoutRewardsPointsUsed']  = $point;
            }

            $result['chechoutRewardsPointsAvailble'] = $this->rewardsData->formatPoints(
                $purchase->getCustomerBalancePoints($purchase->getQuote()->getCustomerId())
            );
            $result['chechoutRewardsPointsMax']      = $purchase->getMaxPointsNumberToSpent();
            $result['chechoutRewardsIsShow']         = $purchase->getEarnPoints() > 0;
        }

        $rewards = $this->rewardsDataFactory->create();
        $rewards->setData($result);

        return $rewards;
    }

    /**
     * @param Purchase $purchase
     * @param string   $shippingCarrier
     * @param string   $shippingMethod
     * @param string   $paymentMethod
     *
     * @return void
     */
    private function refreshPoints(Purchase $purchase, $shippingCarrier, $shippingMethod, $paymentMethod)
    {
        $quote = $purchase->getQuote();
        if (!$quote->getIsVirtual() &&
            empty(trim((string) $quote->getShippingAddress()->getShippingMethod(), '_')) &&
            !empty($shippingCarrier) && !empty($shippingMethod)
        ) {
            $quote->getShippingAddress()->setCollectShippingRates(true)->setShippingMethod(
                $shippingCarrier . '_' . $shippingMethod
            );
            $quote->setCartShippingCarrier($shippingCarrier);
            $quote->setCartShippingMethod($shippingMethod);
        }
        if ($paymentMethod) {
            $address = $quote->getShippingAddress();
            if ($quote->getItemVirtualQty() > 0) {
                $address = $quote->getBillingAddress();
            }
            if (!$address->getPaymentMethod()) {
                $address->setPaymentMethod($paymentMethod);
            }
        }
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
    }

    /**
     * {@inheritdoc}
     */
    public function apply($cartId, $pointsAmount)
    {
        $purchase = $this->rewardsPurchase->getByQuote($cartId);
        if (empty($purchase->getQuote()) || !is_object($purchase->getQuote())) {
            /* @var $quote \Magento\Quote\Model\Quote */
            $quote = $this->quoteRepository->getActive($cartId);
            $purchase->setQuote($quote);
        }
        $this->request->setParams(['points_amount' => $pointsAmount]);

        return $this->rewardsCheckout->processApiRequest($purchase)['success'];
    }

    /**
     * {@inheritdoc}
     */
    public function getBalance($customerId)
    {
        $this->customerRepository->getById($customerId); // validate customer ID

        return $this->rewardsBalance->getBalancePoints($customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getBalances()
    {
        return $this->rewardsBalance->getAllBalances();
    }
}
