<?php
namespace Webkul\Customisation\Model\Api;

class StripeSellerAccountApiManagement implements \Webkul\Customisation\Api\StripeSellerAccountApiManagementInterface
{
    const SEVERE_ERROR = 0;
    const SUCCESS = 1;
    const LOCAL_ERROR = 2;

    /**
     * @var \Webkul\MpStripe\Model\StripeSellerFactory
     */
    protected $stripeSellerFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mphelper;

    /**
     * @var \Webkul\MpStripe\Block\Connect
     */
    protected $block;

    /**
     * @var \Webkul\MpStripe\Helper\Data
     */
    protected $stripeHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @param \Webkul\MpStripe\Model\StripeSellerFactory $stripeSellerFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Webkul\Marketplace\Helper\Data $mphelper
     * @param \Webkul\MpStripe\Block\Connect $block
     */
    public function __construct(
        \Webkul\MpStripe\Block\Connect $block,
        \Webkul\MpStripe\Model\StripeSellerFactory $stripeSellerFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Webkul\Marketplace\Helper\Data $mphelper,
        \Webkul\MpStripe\Helper\Data $stripeHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->stripeSellerFactory = $stripeSellerFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->mphelper = $mphelper;
        $this->block = $block;
        $this->stripeHelper = $stripeHelper;
        $this->timezone = $timezone;
    }

    /**
     * Get stripe save card Api data.
     *
     * @api
     *
     * @return array
     */
    public function getApiData()
    {
        try {
            $data = [];
            $stripeKey = $this->stripeHelper->getConfigValue('api_key');
            \Stripe\Stripe::setApiKey($stripeKey);
            $account = \Stripe\Account::create([
            'country' => 'US',
            'type' => 'standard',
            ]);
            $account_links = \Stripe\AccountLink::create([
                'account' => $account['id'],
                'refresh_url' => 'https://example.com/reauth',
                'return_url' => 'https://example.com/return',
                'type' => 'account_onboarding',
            ]);
            $data[] = [$account_links];
            return $data;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = self::SEVERE_ERROR;
            return $returnArray;
        }
    }
}
