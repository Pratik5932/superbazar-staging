<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mppaypalexpresscheckout
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mppaypalexpresscheckout\Controller\Savepaypal;

use Webkul\Mppaypalexpresscheckout\Model\ResourceModel\Mppaypalexpresscheckout\CollectionFactory;

/**
 * Mppaypalexpresscheckout Savepaypal Index Controller.
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var \Webkul\Mppaypalexpresscheckout\Helper\Data
     */
    private $helper;

    /**
     * @var CollectionFactory
     */
    private $sellerCollectionFactory;

    /**
     * @var \Webkul\Mppaypalexpresscheckout\Model\MppaypalexpresscheckoutFactory
     */
    private $mpexpresscheckoutModel;

    /**
     * @param \Magento\Framework\App\Action\Context                                $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                          $date
     * @param \Webkul\Mppaypalexpresscheckout\Helper\Data                          $helper
     * @param CollectionFactory                                                    $sellerCollectionFactory
     * @param \Webkul\Mppaypalexpresscheckout\Model\MppaypalexpresscheckoutFactory $mpexpresscheckoutModel
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Mppaypalexpresscheckout\Helper\Data $helper,
        CollectionFactory $sellerCollectionFactory,
        \Webkul\Mppaypalexpresscheckout\Model\MppaypalexpresscheckoutFactory $mpexpresscheckoutModel
    ) {
        $this->date = $date;
        $this->helper = $helper;
        $this->sellerCollectionFactory = $sellerCollectionFactory;
        $this->mpexpresscheckoutModel = $mpexpresscheckoutModel;
        parent::__construct($context);
    }

    /**
     * Save seller paypal details in Database
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $postData = $this->getRequest()->getParams();
            $sellerId = $this->helper->getSellerId();
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
                    $this->messageManager->addError(
                        __('PayPal Email already exist for other user')
                    );
                    return $this->resultRedirectFactory->create()->setPath(
                        'marketplace/account/editprofile'
                    );
                }
                $flag =  $this->verifySeller($postData);
                if ($flag) {
                    $data = $this->getSellerCollection($sellerId);
                    
                    if (isset($data) && !empty($data)) {
                        $update = __("Updated");
                        $this->updateDetails($sellerCollection->getId(), $postData, $status, $passwordType);
                    } else {
                        $this->insertDetails($sellerId, $postData, $status, $passwordType);
                    }

                    $this->messageManager->addSuccess(
                        __('Paypal Details Successfully %1', $update)
                    );
                }
            } else {
                $data = $this->getSellerCollection($sellerId);
                if (isset($data) && !empty($data)) {
                    $this->updateDetails($sellerCollection->getId(), $postData, $status, $passwordType);
                    $this->messageManager->addSuccess(
                        __('Successfully removed paypal details')
                    );
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->helper->logDataInLogger(
                "LocalizedException Controller_Savepaypal_index execute : ".$e->getMessage()
            );
            $this->messageManager->addError(__($e->getMessage()));
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Controller_Savepaypal_index execute : ".$e->getMessage());
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $this->resultRedirectFactory->create()->setPath(
            'marketplace/account/editprofile'
        );
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
    private function verifySeller($postData)
    {
        $flag = false;
        if ($this->helper->getConfigValue("verify_seller")) {
            $flag = $this->verifySellerPaypalAccount($postData);
        } else {
            $flag = true;
        }
        return $flag;
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
                $this->messageManager->addError(__('Paypal Details are not saved.'));
            }

            if (!empty($response["responseEnvelope"]["ack"])
                && $response["responseEnvelope"]["ack"] == "Success"
            ) {
                $flag = true;
            } elseif (isset($response['error'])) {
                $errormsg = '';
                foreach ($response['error'] as $key => $value) {
                    $errorId = $value['errorId'];
                    $errorMsg = $value['message'];
                    $errormsg .=  __("ERROR Message : %1 <br/>", urldecode($errorMsg));
                }
                $this->messageManager->addError(
                    __('Invalid PayPal Details. <br/> %1', $errormsg)
                );
            }
            return $flag;
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Controller_Savepaypal_index verifySellerPaypalAccount : ".$e->getMessage());
            return false;
        }
    }
}
