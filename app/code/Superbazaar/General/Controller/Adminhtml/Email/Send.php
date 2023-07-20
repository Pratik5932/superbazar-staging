<?php
/**
*
* Copyright © 2015 Spaargcommerce. All rights reserved.
*/
namespace Superbazaar\General\Controller\Adminhtml\Email;
use Magento\Framework\Mail\Template\TransportBuilder;

class Send extends \Magento\Backend\App\Action
{
    protected $collectionFactory;
    protected $date;
    protected $inlineTranslation;
    protected $_messageManager;
    protected $scopeConfig;
    protected $_resultRedirectFactory;


    const XML_PATH_EXPIRE_DAYS = 'productexpire/general/afterdays';
    const XML_PATH_EMAIL_SENDER = 'productexpire/general/sender_email';
    const XML_PATH_EMAIL_SEND_TO = 'productexpire/general/select_email';

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,

        TransportBuilder $transportBuilder

    ) {
        $this->collectionFactory = $collectionFactory;
        $this->date = $date;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->_resultRedirectFactory = $resultRedirectFactory;

        parent::__construct($context);

    }    
    public function execute()
    {
        $productCollection = $this->collectionFactory->create();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $productCollection->addAttributeToSelect('*');
        $currentDate = $date = $this->date->gmtDate('Y-m-d');
        $days = $this->scopeConfig->getValue(self::XML_PATH_EXPIRE_DAYS, $storeScope)?$this->scopeConfig->getValue(self::XML_PATH_EXPIRE_DAYS, $storeScope): "45";
        $plusOneMonth = date('Y-m-d', strtotime($currentDate . "+$days days"));
        $minusOneMonth = date('Y-m-d', strtotime($currentDate . "-$days days"));
        $productCollection->addFieldToFilter('previous_order_expiry_date', array('from' => $minusOneMonth, 'to' => $plusOneMonth))
        ->addAttributeToSort('previous_order_expiry_date', 'ASC');             
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $emailSender = trim($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope));
        $senderEmail = $this->scopeConfig->getValue('trans_email/ident_'.$emailSender.'/email');
        $senderName = $this->scopeConfig->getValue('trans_email/ident_'.$emailSender.'/name');
        $emailTo = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SEND_TO, $storeScope);
        $recipients = explode(",",$emailTo);
        $subject= "Report Of Products are expiring in next $days days";

        $emailTemplateVariables = array(); 
        $table = "";
        $table .= "<p>The following is a list of products that are expiring (Previous order expiry date) the next $days days</p>";
        $table .= '<table width="800px">
        <thead>
        <th align="left" style="padding: 10px 0;font-size: 14px;border: 1px solid;padding: 5px;">Product Sku</th>
        <th align="left" style="padding: 10px 0;font-size: 14px;border: 1px solid;padding: 5px;">Product Name</th>
        <th align="left" style="padding: 10px 0;font-size: 14px;border: 1px solid;padding: 5px;">Cost price</th>
        <th align="left" style="padding: 10px 0;font-size: 14px;border: 1px solid;padding: 5px;">Selling price</th>
        <th align="left" style="padding: 10px 0;font-size: 14px;border: 1px solid;padding: 5px;">Special price</th>
        <th align="left" style="padding: 10px 0;font-size: 14px;border: 1px solid;padding: 5px;">Special price from date</th>
        <th align="left" style="padding: 10px 0;font-size: 14px;border: 1px solid;padding: 5px;">Special price to date</th>
        <th align="left" style="padding: 10px 0;font-size: 14px;border: 1px solid;padding: 5px;">expiry date</th>
        <th align="left" style="padding: 10px 0;font-size: 14px;border: 1px solid;padding: 5px;">Qty</th>
        </thead>
        ';
        foreach($productCollection as $product){
            $date=$product->getPreviousOrderExpiryDate();
            $date123 = date("F j, Y",strtotime($date));
            $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
            $qty = $StockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());


            $table .="
            <tr> <td align='left' style='border: 1px solid;width: 200px;padding: 5px;'>".$product->getSku()."</td>
            <td align='left' style='border: 1px solid;width: 400px;padding: 5px;'>".$product->getName()."</td>
            <td align='left' style='border: 1px solid;width: 400px;padding: 5px;'>".$product->getPreviousOrderCostPrice()."</td>
            <td align='left' style='border: 1px solid;width: 400px;padding: 5px;'>".$product->getPrice()."</td>
            <td align='left' style='border: 1px solid;width: 400px;padding: 5px;'>".$product->getSpecialPrice()."</td>
            <td align='left' style='border: 1px solid;width: 400px;padding: 5px;'>".$product->getSpecialFromDate()."</td>
            <td align='left' style='border: 1px solid;width: 400px;padding: 5px;'>".$product->getSpecialToDate()."</td>
            <td align='left' style='border: 1px solid;width: 300px;padding: 5px;'>".$date123." </td>

            <td align='left' style='border: 1px solid;padding: 5px;width: 100px'>".$qty."</td>
            </tr>";
        }
        $table .=' </table>';
        $emailTemplateVariables = [
            'data' => $table,
            'subject'    => $subject
        ];
        $this->inlineTranslation->suspend();
        try {

            $postObject = new \Magento\Framework\DataObject();
            if(count($recipients)) {
                foreach($recipients as $recipient) {
                    $transport = $this->transportBuilder
                    ->setTemplateIdentifier('product_expire_send')
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                        ]
                    )
                    ->setTemplateVars($emailTemplateVariables)
                    ->setFrom(['name' =>$senderName,'email' => $senderEmail])
                    ->addTo($recipient)
                    ->getTransport();

                    $transport->sendMessage();
                }
            }
            $this->inlineTranslation->resume();
            $this->messageManager->addSuccessMessage(
                __('Report Has been sent Successfully!')
            );
            $resultRedirect = $this->_resultRedirectFactory->create();

            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;

        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addError(
                __('We can\'t process your request right now. Sorry, that\'s all we know.'.$e->getMessage())
            );
            return $this;

        }
    }
}