<?php
namespace Webkul\Customisation\Model\Api;

class StripeSellerRemoveApiManagement implements \Webkul\Customisation\Api\StripeSellerRemoveApiManagementInterface
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
     * @param \Webkul\MpStripe\Model\StripeSellerFactory $stripeSellerFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Webkul\Marketplace\Helper\Data $mphelper
     * @param \Webkul\MpStripe\Block\Connect $block
     */
    public function __construct(
        \Webkul\MpStripe\Block\Connect $block,
        \Webkul\MpStripe\Model\StripeSellerFactory $stripeSellerFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Webkul\Marketplace\Helper\Data $mphelper

    ) {
        $this->stripeSellerFactory = $stripeSellerFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->mphelper = $mphelper;
        $this->block = $block;
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
            $data = [];
            $integrationType = $this->block->getIntegration();
            $sellerCollection = $this->block->getStripeSellerFactory();
            $accessToken = '';
            foreach ($sellerCollection as $value) {
                $accessToken = $value['access_token'];
            }
            $isPartner = $this->isSeller($id);
            if ($isPartner == 1) {
                $stripeSellerColl = $this->stripeSellerFactory->create()->getCollection()
                                        ->addFieldToFilter("seller_id", ["eq"=>$id]);
                if ($stripeSellerColl && $stripeSellerColl->getSize() > 0) {
                    foreach ($stripeSellerColl as $stripe) {
                        $this->deleteObj($stripe);
                    }
                    $data[] = ['message' => __("Seller Stripe Data Successfully Removed.")];
                } else {
                    $data[] = ['message' => __("Seller Stripe data does not exist.")];
                }
            } else {
                $data[] = ['message' => __("Your request seller does not exist.")];
            }
            return $data;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = self::SEVERE_ERROR;
            return $returnArray;
        }
    }

    public function deleteObj($object)
    {
        $object->delete();
    }

    public function isSeller($id)
    {
        $sellerStatus = 0;
        $model = $this->mphelper->getSellerCollectionObj($id);
        foreach ($model as $value) {
            if ($value->getIsSeller() == 1) {
                $sellerStatus = $value->getIsSeller();
            }
        }
        return $sellerStatus;
    }
}
