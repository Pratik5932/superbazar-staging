<?php
namespace Webkul\Customisation\Model\Api;

use Webkul\Mppaypalexpresscheckout\Model\ResourceModel\Mppaypalexpresscheckout\CollectionFactory;

class PaypalSellerInfoApiManagement implements \Webkul\Customisation\Api\PaypalSellerInfoApiManagementInterface
{
    const SEVERE_ERROR = 0;
    const SUCCESS = 1;
    const LOCAL_ERROR = 2;

    /**
     * @var CollectionFactory
     */
    protected $sellerCollectionFactory;

    /**
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param CollectionFactory $sellerCollectionFactory
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        CollectionFactory $sellerCollectionFactory

    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sellerCollectionFactory = $sellerCollectionFactory;
    }

    /**
     * Get paypal seller info Api data.
     *
     * @api
     *
     * @param int $id
     * @return array
     */
    public function getApiData($id)
    {
        try {
            $sellercollection = $this->sellerCollectionFactory->create()
                ->addFieldToFilter(
                    'seller_id',
                    $id
                );
            $data = [];
            if ($sellercollection && $sellercollection->getSize() > 0) {
                $isSellerCreatedPaypalAccount = true;
                foreach ($sellercollection as $data) {
                    $eachData = [
                        'paypal_id' => $data['paypal_id'],
                        'paypal_fname' => $data['paypal_fname'],
                        'paypal_lname' => $data['paypal_lname'],
                        'paypal_merchant_id' => $data['paypal_merchant_id'],
                        'paypal_status' => $data['status']
                    ];
                    $data = [$eachData];
                }
                return $data;
            } else {
                $data[] = ['message' => __("No data found")];
                return $data;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = self::SEVERE_ERROR;
            return $returnArray;
        }
    }
}
