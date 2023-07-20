<?php
/**
* Copyright © Magento, Inc. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Superbazaar\General\Controller\Add;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\ProductAlert\Controller\Add as AddController;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
* Controller for notifying about stock.
*/
class Stock extends \Magento\ProductAlert\Controller\Add\Stock
{
    /**
    * @var \Magento\Catalog\Api\ProductRepositoryInterface
    */
    protected $productRepository;

    /**
    * @var StoreManagerInterface
    */
    protected $storeManager;

    /**
    * @param \Magento\Framework\App\Action\Context $context
    * @param \Magento\Customer\Model\Session $customerSession
    * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    * @param StoreManagerInterface|null $storeManager
    */
    /*public function __construct(
    CustomerSession $customerSession,
    ProductRepositoryInterface $productRepository,
    StoreManagerInterface $storeManager = null
    ) {
    $this->productRepository = $productRepository;
    $this->storeManager = $storeManager ?: $this->_objectManager
    ->get(\Magento\Store\Model\StoreManagerInterface::class);
    }*/

    /**
    * Method for adding info about product alert stock.
    *
    * @return \Magento\Framework\Controller\Result\Redirect
    */
    public function execute()
    {
        $backUrl = $this->getRequest()->getParam(Action::PARAM_NAME_URL_ENCODED);
        $productId = (int)$this->getRequest()->getParam('product_id');
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$backUrl || !$productId) {
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }

        try {
            /* @var $product \Magento\Catalog\Model\Product */
            $product = $this->productRepository->getById($productId);
            $store = $this->_objectManager
            ->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore();
            /** @var \Magento\ProductAlert\Model\Stock $model */
            $model = $this->_objectManager->create(\Magento\ProductAlert\Model\Stock::class)
            ->setCustomerId($this->customerSession->getCustomerId())
            ->setProductId($product->getId())
            ->setWebsiteId($store->getWebsiteId())
            ->setStoreId($store->getId());
            $model->save();
            $this->inlineTranslation = $this->_objectManager->get('\Magento\Framework\Translate\Inline\StateInterface');
            $this->transportBuilder = $this->_objectManager->get('\Magento\Framework\Mail\Template\TransportBuilder');
            $this->scopeConfig = $this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');

            #$emailSender = trim($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope));
            $senderEmail = $this->scopeConfig->getValue('trans_email/ident_custom1/email');
            $senderName = $this->scopeConfig->getValue('trans_email/ident_custom1/name');
            $emailTo = $senderEmail;

            # $recipients = explode(",",$emailTo);
            $subject= "Product Out of stock alert";

            $emailTemplateVariables = array(); 

            $table = "";
            $table .= "<p>Out of Stock alert subscribed!!</p>";

            $table .= '<table width="800px">
            <thead>
            <th align="left" style="padding: 10px 0;font-size: 14px;border: 1px solid;padding: 5px;">Customer Name</th>
            <th align="left" style="padding: 10px 0;font-size: 14px;border: 1px solid;padding: 5px;">Email</th>
            <th align="left" style="padding: 10px 0;font-size: 14px;border: 1px solid;padding: 5px;">Product Name</th>
            <th align="left" style="padding: 10px 0;font-size: 14px;border: 1px solid;padding: 5px;">Product Sku</th>
            </thead>
            ';
            $table .="
            <tr> <td align='left' style='border: 1px solid;width: 200px;padding: 5px;'>".$this->customerSession->getCustomer()->getName()."</td>
            <td align='left' style='border: 1px solid;width: 400px;padding: 5px;'>".$this->customerSession->getCustomer()->getEmail()."</td>
            <td align='left' style='border: 1px solid;padding: 5px;width: 100px'>".$product->getName()."</td>
            <td align='left' style='border: 1px solid;width: 300px;padding: 5px;'>".$product->getSku()." </td>
            </tr>";
            $table .=' </table>';


            $emailTemplateVariables = [
                'data' => $table,
                'subject'    => $subject
            ];

            $this->inlineTranslation->suspend();

            $postObject = new \Magento\Framework\DataObject();
            $transport = $this->transportBuilder
            ->setTemplateIdentifier('stock_notofication')
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom(['name' =>$senderName,'email' => $senderEmail])
            ->addTo($senderEmail,$senderName)
            ->getTransport();

            $transport->sendMessage();

            $sellerproduct = $this->_objectManager->create('Webkul\Marketplace\Model\Product')->load($product->getId(),'mageproduct_id');
            if($sellerproduct->getId()){
                $seller = $this->_objectManager->get('Magento\Customer\Model\Customer')->load($sellerproduct->getData('seller_id'));
                $sellerEmail = $seller->getEmail(); 
                $sellerName = $seller->getSellerName(); 
                $transport1 = $this->transportBuilder
                ->setTemplateIdentifier('product_expire_send')
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars($emailTemplateVariables)
                ->setFrom(['name' =>$senderName,'email' => $senderEmail])
                ->addTo($sellerEmail,$sellerName)
                ->getTransport();

                $transport1->sendMessage();
            }

            $this->inlineTranslation->resume();


            $this->messageManager->addSuccess(__('Alert subscription has been saved.'));
        } catch (NoSuchEntityException $noEntityException) {
            $this->messageManager->addError(__('There are not enough parameters.'));
            $resultRedirect->setUrl($backUrl);
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __("The alert subscription couldn't update at this time. Please try again later.")
            );
        }
        $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
        return $resultRedirect;
    }
}
