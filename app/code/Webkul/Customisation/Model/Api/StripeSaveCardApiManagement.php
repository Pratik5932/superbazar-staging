<?php
namespace Webkul\Customisation\Model\Api;

class StripeSaveCardApiManagement implements \Webkul\Customisation\Api\StripeSaveCardApiManagementInterface
{
    const SEVERE_ERROR = 0;
    const SUCCESS = 1;
    const LOCAL_ERROR = 2;

    /**
     * @var \Webkul\MpStripe\Model\ResourceModel\StripeCustomer\CollectionFactory
     */
    protected $stripeCustomerFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Webkul\MpStripe\Helper\Data
     */
    protected $helper;

    /**
     * @param \Webkul\MpStripe\Model\ResourceModel\StripeCustomer\CollectionFactory $stripeCustomerFactory,
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Webkul\MpStripe\Helper\Data $helper
     */
    public function __construct(
        \Webkul\MpStripe\Model\ResourceModel\StripeCustomer\CollectionFactory $stripeCustomerFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Webkul\MpStripe\Helper\Data $helper

    ) {
        $this->stripeCustomerFactory = $stripeCustomerFactory;
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
    public function getApiData($id)
    {
        try {
            $customerId = $id;
            $cardData = $this->stripeCustomerFactory->create()
                ->addFieldToFilter('customer_id', ['eq' => $customerId]);
            $data = [];
            if ($cardData->getSize() > 0) {
                foreach ($cardData as $card) {
                    $eachData = [];
                    $eachData = [
                        'entity_id' => $card->getEntityId(),
                        'card_number' => '****'.$card->getData('last4'),
                        'status' => $this->helper->customerExist($card->getData('stripe_customer_id')) ? __('Enable') : __('Expire'),
                    ];
                    $data[] = $eachData;
                }
            } else {
                $data[] = [
                    'message' => __("No cards available")
                ];
            }
            return $data;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = self::SEVERE_ERROR;
            return $returnArray;
        }
    }
}
