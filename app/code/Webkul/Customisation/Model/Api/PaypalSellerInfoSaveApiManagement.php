<?php
namespace Webkul\Customisation\Model\Api;

use Webkul\Mppaypalexpresscheckout\Model\ResourceModel\Mppaypalexpresscheckout\CollectionFactory;

class PaypalSellerInfoSaveApiManagement implements \Webkul\Customisation\Api\PaypalSellerInfoSaveApiManagementInterface
{
    const SEVERE_ERROR = 0;
    const SUCCESS = 1;
    const LOCAL_ERROR = 2;

    /**
     * @var \Webkul\Mppaypalexpresscheckout\Model\MppaypalexpresscheckoutFactory
     */
    protected $mpexpresscheckoutModel;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Webkul\Mppaypalexpresscheckout\Helper\Data
     */
    protected $helper;

    /**
     * @var CollectionFactory
     */
    protected $sellerCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @param \Webkul\Mppaypalexpresscheckout\Model\MppaypalexpresscheckoutFactory $mpexpresscheckoutModel
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Webkul\Mppaypalexpresscheckout\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date,
     * @param CollectionFactory $sellerCollectionFactory
     */
    public function __construct(
        \Webkul\Mppaypalexpresscheckout\Model\MppaypalexpresscheckoutFactory $mpexpresscheckoutModel,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Webkul\Mppaypalexpresscheckout\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        CollectionFactory $sellerCollectionFactory

    ) {
        $this->mpexpresscheckoutModel = $mpexpresscheckoutModel;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        $this->date = $date;
        $this->sellerCollectionFactory = $sellerCollectionFactory;
    }

    /**
     * Get stripe save card Api data.
     *
     * @api
     *
     * @return array
     */
    public function getApiData($paypal_id, $paypal_fname, $paypal_lname, $paypal_merchant_id, $seller_id)
    {
        try {
            $postData = [
                'paypal_id' => $paypal_id,
                'paypal_fname' => $paypal_fname,
                'paypal_lname' => $paypal_lname,
                'paypal_merchant_id' => $paypal_merchant_id
            ];
            $sellerId = $seller_id;
            $passwordType = false;
            $update = __("Added");
            $enabledStatus =  \Webkul\Mppaypalexpresscheckout\Model\Mppaypalexpresscheckout::STATUS_ENABLED;
            $disabledStatus = \Webkul\Mppaypalexpresscheckout\Model\Mppaypalexpresscheckout::STATUS_DISABLED;
            $status = $enabledStatus;
            if (!$this->helper->getConfigValue("verify_seller")
                && $this->helper->getConfigValue("details_approval")
            ) {
                $status = $disabledStatus;
            }

            if (count(array_unique(explode('*', $postData['paypal_merchant_id']))) == 1
                && strrpos($postData['paypal_merchant_id'], '*')!==false
            ) {
                $passwordType = true;
            }
            if ($postData['paypal_id'] && $postData['paypal_id']!=="") {
                $paypalExists = $this->helper->checkPaypalIdExistsOrNot($postData['paypal_id'], $sellerId);
                if ($paypalExists) {
                    $data[] = [
                        'message' => __('PayPal Email already exist for other user')
                    ];
                    return $data;
                }
                $verifyData =  $this->verifySeller($postData);
                if ($verifyData['flag']) {
                    $sellerData = $this->getSellerCollection($sellerId);
                    if (isset($sellerData) && !empty($sellerData)) {
                        $update = __("Updated");
                        $this->updateDetails($sellerCollection->getId(), $postData, $status, $passwordType);
                    } else {
                        $this->insertDetails($sellerId, $postData, $status, $passwordType);
                    }
                    $data[] = [
                        'message' => __('Paypal Details Successfully %1', $update)
                    ];
                } else {
                    $data[] = [
                        'message' => $verifyData['message']
                    ];
                }
                return $data;
            } else {
                $data = $this->getSellerCollection($sellerId);
                if (isset($data) && !empty($data)) {
                    $this->updateDetails($sellerCollection->getId(), $postData, $status, $passwordType);
                    $data = [
                        'message' => __('Successfully removed paypal details')
                    ];
                }
                return $data;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = self::SEVERE_ERROR;
            return $returnArray;
        }
    }

    private function verifySeller($postData)
    {
        $data = [];
        if ($this->helper->getConfigValue("verify_seller")) {
            $data = $this->verifySellerPaypalAccount($postData);
        } else {
            $data = ['flag' => false];
        }
        return $data;
    }

    public function verifySellerPaypalAccount($postData)
    {
        try {
            $response = $this->helper->paypalAccountCheck(
                $postData['paypal_id'],
                $postData['paypal_fname'],
                $postData['paypal_lname']
            );
            $flag = false;
            if ($response === false) {
                $data = [
                    'message' => __('Paypal Details are not saved.'),
                    'flag' => $flag
                ];
            }

            if (!empty($response["responseEnvelope"]["ack"])
                && $response["responseEnvelope"]["ack"] == "Success"
            ) {
                $flag = true;
                $data = [
                    'flag' => $flag
                ];
            } elseif (isset($response['error'])) {
                $errormsg = '';
                foreach ($response['error'] as $key => $value) {
                    $errorId = $value['errorId'];
                    $errorMsg = $value['message'];
                    $errormsg .=  __("ERROR Message : %1 <br/>", urldecode($errorMsg));
                }
                $data = [
                    'message' => __($errormsg),
                    'flag' => $flag
                ];
            }
            return $data;
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Controller_Savepaypal_index verifySellerPaypalAccount : ".$e->getMessage());
            return false;
        }
    }

    private function getSellerCollection($sellerId)
    {
        $data = [];
        $sellerCollection = $this->sellerCollectionFactory->create()
            ->addFieldToFilter('seller_id', $sellerId);
        foreach ($sellerCollection as $model) {
            $data = $model->getData();
        }
        return $data;
    }

    public function updateDetails($id, $postData, $status, $passwordType)
    {
        try {
            $data = $this->mpexpresscheckoutModel->create()->load($id);
            $data->setPaypalId($postData['paypal_id']);
            $data->setPaypalFname($postData['paypal_fname']);
            $data->setPaypalLname($postData['paypal_lname']);
            if (!$passwordType) {
                $data->setPaypalMerchantId($postData['paypal_merchant_id']);
            }
            $data->setUpdatedAt($this->date->gmtDate());
            $data->setStatus($status);
            $data->save();
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Controller_Savepaypal_index updateDetails : ".$e->getMessage());
        }
    }

    public function insertDetails($sellerId, $postData, $status, $passwordType)
    {
        try {
            $collection1 = $this->mpexpresscheckoutModel->create();
            $collection1->setSellerId($sellerId);
            $collection1->setPaypalId($postData['paypal_id']);
            $collection1->setPaypalFname($postData['paypal_fname']);
            $collection1->setPaypalLname($postData['paypal_lname']);
            if (!$passwordType) {
                $collection1->setPaypalMerchantId($postData['paypal_merchant_id']);
            }
            $collection1->setCreatedAt($this->date->gmtDate());
            $collection1->setUpdatedAt($this->date->gmtDate());
            $collection1->setStatus($status);
            $collection1->save();
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Controller_Savepaypal_index insertDetails : ".$e->getMessage());
        }
    }
}
