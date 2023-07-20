<?php
namespace Webkul\Customisation\Model\Api;

class StripeDeleteSaveCardApiManagement implements \Webkul\Customisation\Api\StripeDeleteSaveCardApiManagementInterface
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
    public function getApiData($id, $cardId)
    {
        try {
            $data = [];
            $customerId = $id;
            $response = $this->deleteCards($cardId, $customerId);
            if ($response) {
                $data[] = [
                    'message' => __('Card successfully deleted')
                ];
            } else {
                $data[] = [
                    'message' =>__('Not able to delete the cards')
                ];
            }
            return $data;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = self::SEVERE_ERROR;
            return $returnArray;
        }
    }

    /**
     * deleteCards function to delete cards of the customer.
     *
     * @return bool
     */
    public function deleteCards($cardId, $customerId = 0)
    {
        if ($customerId) {
            $collection = $this->stripeCustomerModel->create()->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $customerId])
                ->addFieldToFilter('entity_id', ['eq' => $cardId]);
            if ($collection->getSize() > 0) {
                foreach ($collection as $card) {
                    try {
                        $this->deleteCard($card);
                    } catch (\Exception $e) {
                        return $e->getMessage();
                    }
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    /**
     * method to delete card
     *
     * @param object $card
     */
    public function deleteCard($card)
    {
        $card->delete();
    }
}
