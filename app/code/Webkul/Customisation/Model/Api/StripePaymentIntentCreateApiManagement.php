<?php
namespace Webkul\Customisation\Model\Api;

class StripePaymentIntentCreateApiManagement implements \Webkul\Customisation\Api\StripePaymentIntentCreateApiManagementInterface
{
    const SEVERE_ERROR = 0;
    const SUCCESS = 1;
    const LOCAL_ERROR = 2;

    /**
     * @var \Webkul\MpStripe\Model\StripeCustomerFactory
     */
    protected $stripeCustomerModel;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Webkul\MpStripe\Helper\Data
     */
    protected $helper;

    /**
     * @param \Webkul\MpStripe\Model\StripeCustomerFactory $stripeCustomerModel
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Webkul\MpStripe\Helper\Data $helper
     */
    public function __construct(
        \Webkul\MpStripe\Model\StripeCustomerFactory $stripeCustomerModel,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Webkul\MpStripe\Helper\Data $helper

    ) {
        $this->stripeCustomerModel = $stripeCustomerModel;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
    }

    /**
     * Get stripe save card Api data.
     *
     * @api
     *
     * @return array
     */
    public function getApiData($amount, $currency, $sellerStripeAccountId)
    {
        try {
            $stripeKey = $this->helper->getConfigValue('api_key');
            \Stripe\Stripe::setApiKey($stripeKey);
            $payment_intent = \Stripe\PaymentIntent::create([
                'payment_method_types' => ['card'],
                'amount' => $amount,
                'currency' => $currency,
                'application_fee_amount' => $amount,
                'transfer_data' => [
                'destination' => $sellerStripeAccountId,
                ],
            ]);
            $data[] = [
                'client_secret' => $payment_intent->client_secret
            ];
            return $data;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = self::SEVERE_ERROR;
            return $returnArray;
        }
    }
}
