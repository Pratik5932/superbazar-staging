<?php
/**
*
* Copyright © 2015 Spaargcommerce. All rights reserved.
*/
namespace Superbazaar\General\Cron;
use Magento\Framework\Mail\Template\TransportBuilder;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as MpProductCollection;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory as SellerCollection;
use Webkul\Marketplace\Model\Product as SellerProduct;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;


class ProductExpireSend
{
    protected $collectionFactory;
    protected $date;
    protected $inlineTranslation;
    protected $_messageManager;
    protected $scopeConfig;
    protected $customerRepository;
    protected $fileFactory;
    protected $resultLayoutFactory;
    protected $csvProcessor;
    protected $directoryList;



    const XML_PATH_EXPIRE_DAYS = 'productexpire/general/afterdays';
    const XML_PATH_EMAIL_SENDER = 'productexpire/general/sender_email';
    const XML_PATH_EMAIL_SEND_TO = 'productexpire/general/select_email';

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        MpProductCollection $mpProductCollectionFactory,
        SellerCollection $sellerCollectionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\Filesystem $filesystem,

        TransportBuilder $transportBuilder

    ) {
        $this->collectionFactory = $collectionFactory;
        $this->date = $date;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->_mpProductCollectionFactory = $mpProductCollectionFactory;
        $this->_sellerCollectionFactory = $sellerCollectionFactory;
        $this->customerRepository = $customerRepositoryFactory;
        $this->fileFactory = $fileFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->csvProcessor = $csvProcessor;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);;



    }    
    public function getColumnHeader() {
        $headers = ['store_location','SKU','Product name','Status','Cost price','Selling price','Special price','Special price from date','Special price to date','expiry date','Qty'];
        return $headers;
    }

    public function getColumnHeader1() {
        $headers = ['Status','SKU','Product name','Cost price','Qty','stock status',"previous_order_expiry_date"];
        return $headers;
    }

    public function getColumnHeaderForInventoryReport() {
        $headers = ['sku','name','previous_order_cost_price','previous_order_expiry_date','price','product_online','store_location','supplier','qty','value'];
        return $headers;
    }
    public function getColumnHeaderForSalesReport() {
        $headers = ['ID','Purchase Point','Purchase Date','Bill-to Name','Ship-to Name','Grand Total (Purchased)','Status','Shipping Address','Shipping Information','Customer Email','Shipping and Handling','Payment Method','Total Tax','Phone Number','Ordr Place Point'];
        return $headers;
    }
    public function getColumnHeaderForSalesReport2() {
        $headers = ['ID','Purchase Point','Purchase Date','Bill-to Name','Ship-to Name','Grand Total (Purchased)','Status','Shipping Address','Shipping Information','Customer Email','Shipping and Handling','Payment Method',"Payment fee",'Shipping postcode','Total Tax','Phone Number','Ordr Place Point'];
        return $headers;
    }
    public function getColumnHeaderForSalesReport1() {
        $headers = ['Order ref (invoice number)','Name','Shipping Address','Postcode','Amount','Order status','Payment Method','GST / Tax'];
        return $headers;
    }
    public function exportToCsv(){
        # mail("er.bharatmali@gmail.com","cron run","test");
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $sellerCollection = $this->_sellerCollectionFactory->create();
        // $sellerCollection->addAttributeToFilter('seller_id', ['in' => $querydata->getData()]);;
        $currentDate = $date = $this->date->gmtDate('Y-m-d');
        #echo $currentDate;
        $days = $this->scopeConfig->getValue(self::XML_PATH_EXPIRE_DAYS, $storeScope)?$this->scopeConfig->getValue(self::XML_PATH_EXPIRE_DAYS, $storeScope): "45";
        $plusOneMonth = date('Y-m-d', strtotime($currentDate . "90 days"));
        $minusOneMonth = date('Y-m-d', strtotime($currentDate . "-90 days"));
        # echo $plusOneMonth;exit;

        # echo $sellerCollection->getSize();exit;
        $storeLocation = array("1022","6453","7023");
        $csv =[];

        /* Open file */


        /* Write Header */
        $columns = $this->getColumnHeader();
        foreach ($columns as $column) {
            $header[] = $column;
        }
        foreach($storeLocation as $postcode){

            if($postcode == 1022){
                $customerName = "3024";
                $sellerEmail = "routificnineteens@gmail.com";
                #$sellerEmail = "testingdevcheck@gmail.com";
                $sellername = "Jaydeep Arja";
            }
            if($postcode == 6453){
                $customerName = "3030";
                #$sellerEmail = "er.bharatmali@gmail.com";
                $sellerEmail = "srisairam@superbazaar.com.au";
                $sellername = "Mr Sai Balaji SuperBazaar WS";
            }
            if($postcode == 7023){
                $customerName = "3064";
                # $sellerEmail = "sharda0728@gmail.com";
                $sellerEmail = "admin3064@superbazaar.com.au";
                $sellername = "Superbazaar 3064";
            }

            $name = date('m_d_y')."_".$customerName;

            $this->directory->create('export');
            $filepath = 'export/seller/product_' . $name . '.csv';
            $stream = $this->directory->openFile($filepath, 'w+');
            $stream->lock();

            $stream->writeCsv($header);
            $emailSender = trim($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope));
            $senderEmail = $this->scopeConfig->getValue('trans_email/ident_'.$emailSender.'/email');
            $senderName = $this->scopeConfig->getValue('trans_email/ident_'.$emailSender.'/name');

            $currentDate = $date = $this->date->gmtDate('Y-m-d');
            $days = $this->scopeConfig->getValue(self::XML_PATH_EXPIRE_DAYS, $storeScope)?$this->scopeConfig->getValue(self::XML_PATH_EXPIRE_DAYS, $storeScope): "45";
            $plusOneMonth = date('Y-m-d', strtotime($currentDate . "90 days"));
            $minusOneMonth = date('Y-m-d', strtotime($currentDate . "-90 days"));


            #echo $minusOneMonth;exit;

            $productCollection = $this->collectionFactory->create();
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $productCollection->addAttributeToSelect('*');
            $productCollection-> addAttributeToFilter('store_location', ['eq' => $postcode]);
            $productCollection->addFieldToFilter('previous_order_expiry_date', array('from' => $minusOneMonth, 'to' => $plusOneMonth))
            ->addAttributeToSort('previous_order_expiry_date', 'ASC'); 


            #echo $productCollection->getSelect()->__toString();exit;

            if($productCollection->getSize()){


                foreach ($productCollection as $item) {
                    $date=$item->getPreviousOrderExpiryDate();

                    $date123 = date("F j, Y",strtotime($date));
                  //  $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
                   // $qty = $StockState->getStockQty("27015", $item->getStore()->getWebsiteId());
					
					$productStockObj = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($item->getId());
					$qty = $productStockObj->getQty();

					#echo $qty;exit;

                    $itemData = [];
                    $itemData[] = $customerName;
                    $itemData[] = $item->getEntityId();
                    $itemData[] = $item->getSku();
                    $itemData[] = $item->getName();
                    $itemData[] = $item->getPreviousOrderCostPrice();
                    $itemData[] = $item->getPrice();
                    $itemData[] = $item->getSpecialPrice();
                    $itemData[] = $item->getSpecialFromDate();
                    $itemData[] = $item->getSpecialToDate();
                    $itemData[] =$date123;
                    $itemData[] =$qty;
                    $stream->writeCsv($itemData);

                }


            }
            try{
                $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
                $myfile = $storeManager->getStore()->getBaseUrl()."var/".$filepath;
                $body = "
                <div>
                <p>Report of Products are expiring in next 90 days</p>
                <p><b>Click here to donload Report</b><a style='background: #59b210;padding: 5px 10px;color: #fff;margin-left: 10px;text-decoration: none;'href=".$myfile.">Download File</a></p>
                </div>";
                $to = ['er.bharatmali@gmail.com'];
                // $email = new \Zend_Mail();
                // $email->setSubject("Report of Products are expiring in next 90 days");
                // $email->setBodyHtml($body);
                // $email->setFrom($senderEmail, $senderName);
                // $email->addTo($sellerEmail, $sellername);
                // $email->send();
                /*send email*/
                 $templateVars = [
                        'myfile' => $myfile
                    ];

        $storeId = 1;
        $templateId = '9';

        $sender = [
            'name' => $senderName,
            'email' => $senderEmail
        ];

        $recipient = [
            'name' => $sellername,
            'email' => $sellerEmail
        ];

        $this->inlineTranslation->suspend();

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId,
            ])
            ->setTemplateVars($templateVars)
            ->setFrom($sender)
            ->addTo($recipient['email'], $recipient['name'])
            ->getTransport();

            $transport->sendMessage();

            } catch (\Exception $e) {
                $this->inlineTranslation->resume();
                $this->messageManager->addError(
                    __('We can\'t process your request right now. Sorry, that\'s all we know.'.$e->getMessage())
                );
                return $this;

            }


        }

        $name = date('m_d_y')."_All1";

        $this->directory->create('export');
        $filepath = 'export/seller/product_' . $name . '.csv';
        $stream1 = $this->directory->openFile($filepath, 'w+');
        $stream1->lock();

        $stream1->writeCsv($header);


        $currentDate = $date = $this->date->gmtDate('Y-m-d');
        $days = $this->scopeConfig->getValue(self::XML_PATH_EXPIRE_DAYS, $storeScope)?$this->scopeConfig->getValue(self::XML_PATH_EXPIRE_DAYS, $storeScope): "45";
        $plusOneMonth = date('Y-m-d', strtotime($currentDate . "90 days"));
        $minusOneMonth = date('Y-m-d', strtotime($currentDate . "-90 days"));
        $productCollection1 = $this->collectionFactory->create();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $productCollection1->addAttributeToSelect('*');
        $productCollection1->addAttributeToFilter('store_location', ['neq' => null]);
        #$productCollection1->addFieldToFilter('previous_order_expiry_date', array('from' => $minusOneMonth, 'to' => $plusOneMonth))
        # ->addAttributeToSort('store_location', 'ASC')
        #->addAttributeToSort('previous_order_expiry_date', 'ASC');


        #echo $productCollection1->getSelect()->__toString();exit;
        if($productCollection1->getSize()){

            foreach ($productCollection1 as $item) {
                $date=$item->getPreviousOrderExpiryDate();

                $date123 = date("F j, Y",strtotime($date));
               // $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
              //  $qty = $StockState->getStockQty($item->getId(), $item->getStore()->getWebsiteId());
               $productStockObj = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($item->getId());
					$qty = $productStockObj->getQty();

                $itemData1 = [];
                $attribute = $item->getResource()->getAttribute('store_location');
                $storel = $attribute->getSource()->getOptionText($item->getStoreLocation());
                $itemData1[] = $storel;
               # $itemData1[] = $item->getEntityId();
                $itemData1[] = $item->getSku();
                $itemData1[] = $item->getName();
				$itemData1[] = $item->getStatus();
                $itemData1[] = $item->getPreviousOrderCostPrice();
                $itemData1[] = $item->getPrice();
                $itemData1[] = $item->getSpecialPrice();
                $itemData1[] = $item->getSpecialFromDate();
                $itemData1[] = $item->getSpecialToDate();
                $itemData1[] =$date123;
                $itemData1[] =$qty;
                $stream1->writeCsv($itemData1);




            }

        }
        #  print_R($itemData);exit;
        try {
            // $emailTo = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SEND_TO, $storeScope);
            $emailTo = "naveen_arja@yahoo.co.uk";
            #$emailTo = "er.bharatmali@gmail.com";
            $myfile = $storeManager->getStore()->getBaseUrl()."var/".$filepath;
            $body = "
            <div>
            <p>Report of Products are expiring in next 90 days</p>
            <p><b>Click here to donload Report</b><a style='background: #59b210;padding: 5px 10px;color: #fff;margin-left: 10px;text-decoration: none;'href=".$myfile.">Download File</a></p>
            </div>";
            // $email = new \Zend_Mail();
            // $email->setSubject("Report of Products are expiring in next 90 days");
            // $email->setBodyHtml($body);
            // $email->setFrom($senderEmail, $senderName);
            // $email->addTo($emailTo, "Admin");
            // $email->send();

            /*email send */

             $templateVars = [
                        'myfile' => $myfile
                    ];

        $storeId = 1;
        $templateId = '9';

        $sender = [
            'name' => $senderName,
            'email' => $senderEmail
        ];

        $recipient = [
            'name' => 'Admin',
            'email' => $emailTo
        ];

        $this->inlineTranslation->suspend();

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId,
            ])
            ->setTemplateVars($templateVars)
            ->setFrom($sender)
            ->addTo($recipient['email'], $recipient['name'])
            ->getTransport();

            $transport->sendMessage();

        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addError(
                __('We can\'t process your request right now. Sorry, that\'s all we know.'.$e->getMessage())
            );
            return $this;

        }

        /*if($sellerCollection->getSize()){
        foreach($sellerCollection as $seller){

        $customerName = $this->customerRepository->getById($seller->getSellerId())->getFirstname();

        // if($seller->getSellerId() == "219"){
        # echo $seller->getSellerId();exit;
        $querydata = $this->_mpProductCollectionFactory->create()->
        addFieldToFilter('seller_id', $seller->getSellerId())
        ->addFieldToFilter('status', ['neq' => SellerProduct::STATUS_DISABLED])
        ->addFieldToSelect('mageproduct_id')
        ->setOrder('mageproduct_id');

        #print_r($querydata->getData());

        $sellerproductCollection =$this->collectionFactory->create();
        $sellerproductCollection->addAttributeToSelect('*');
        $sellerproductCollection->addAttributeToFilter('entity_id', ['in' => $querydata->getData()]);
        $sellerproductCollection->addAttributeToFilter('status', ['neq' => SellerProduct::STATUS_DISABLED]);
        $sellerproductCollection->addFieldToFilter('previous_order_expiry_date', array('from' => $currentDate, 'to' => $plusOneMonth))
        ->addAttributeToSort('previous_order_expiry_date', 'ASC');
        $sellerproductCollection->addStoreFilter();

        # echo $sellerproductCollection->getSelect()->__toString();exit;
        if($sellerproductCollection->getSize()){

        $name = date('m_d_y').$customerName;
        $filepath = 'export/seller/product_' . $name . '.csv';
        $this->directory->create('export');
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        $columns = $this->getColumnHeader();
        foreach ($columns as $column) {
        $header[] = $column;
        }
        $stream->writeCsv($header);

        foreach ($sellerproductCollection as $item) {
        $date=$item->getPreviousOrderExpiryDate();

        $date123 = date("F j, Y",strtotime($date));
        $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
        $qty = $StockState->getStockQty($item->getId(), $item->getStore()->getWebsiteId());

        $itemData = [];
        $itemData[] = $customerName;
        $itemData[] = $item->getEntityId();
        $itemData[] = $item->getSku();
        $itemData[] = $item->getName();
        $itemData[] = $item->getPreviousOrderCostPrice();
        $itemData[] = $item->getPrice();
        $itemData[] = $item->getSpecialPrice();
        $itemData[] = $item->getSpecialFromDate();
        $itemData[] = $item->getSpecialToDate();
        $itemData[] =$date123;
        $itemData[] =$qty;
        $stream->writeCsv($itemData);


        }


        /*$content['type'] = 'filename';
        $content['value'] = $filepath;
        $content['rm'] = 1;
        $this->fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
        # $this->exportToCsv($sellerproductCollection,$seller->getId());
        }//}


        }
        //}
        }*/
    }

    public function sendInventoryReport(){
        mail("er.bharatmali@gmail.com","Inventory-Report","test");
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $sellerCollection = $this->_sellerCollectionFactory->create();
        // $sellerCollection->addAttributeToFilter('seller_id', ['in' => $querydata->getData()]);;
        $currentDate = $date = $this->date->gmtDate('Y-m-d');
        #echo $currentDate;
        $days = $this->scopeConfig->getValue(self::XML_PATH_EXPIRE_DAYS, $storeScope)?$this->scopeConfig->getValue(self::XML_PATH_EXPIRE_DAYS, $storeScope): "45";
        $currentDate = $date = $this->date->gmtDate('Y-m-d');
        $minusOneMonth = date('Y-m-d', strtotime($currentDate . "-1 month"));
        # echo $plusOneMonth;exit;

        # echo $sellerCollection->getSize();exit;
        $storeLocation = array("1022","6453","7023");
        $csv =[];

        /* Open file */


        /* Write Header */
        $columns = $this->getColumnHeaderForInventoryReport();
        foreach ($columns as $column) {
            $header[] = $column;
        }
        foreach($storeLocation as $postcode){

            if($postcode == 1022){
                $customerName = "3024";
                $sellerEmail = "routificnineteens@gmail.com";
                #$sellerEmail = "testingdevcheck@gmail.com";
                $sellername = "Jaydeep Arja";
            }
            if($postcode == 6453){
                $customerName = "3030";
                #$sellerEmail = "er.bharatmali@gmail.com";
                $sellerEmail = "srisairam@superbazaar.com.au";
                $sellername = "Mr Sai Balaji SuperBazaar WS";
            }
            if($postcode == 7023){
                $customerName = "3064";
                #$sellerEmail = "sharda0728@gmail.com";
                $sellerEmail = "admin3064@superbazaar.com.au";
                $sellername = "Superbazaar 3064";
            }

            $name = date('m_d_y')."_".$customerName;

            $this->directory->create('export');
            $filepath = 'export/inventory_report/inventory_report' . $name . '.csv';
            $stream = $this->directory->openFile($filepath, 'w+');
            $stream->lock();

            $stream->writeCsv($header);
            $emailSender = trim($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope));
            $senderEmail = $this->scopeConfig->getValue('trans_email/ident_'.$emailSender.'/email');
            $senderName = $this->scopeConfig->getValue('trans_email/ident_'.$emailSender.'/name');

            $currentDate = $date = $this->date->gmtDate('Y-m-d');
            $days = $this->scopeConfig->getValue(self::XML_PATH_EXPIRE_DAYS, $storeScope)?$this->scopeConfig->getValue(self::XML_PATH_EXPIRE_DAYS, $storeScope): "45";
            $currentDate = $date = $this->date->gmtDate('Y-m-d');
            $minusOneMonth = date('Y-m-d', strtotime($currentDate . "-1 month"));

            $productCollection = $this->collectionFactory->create();
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $productCollection->addAttributeToSelect('*');
            $productCollection-> addAttributeToFilter('store_location', ['eq' => $postcode]);
            #$productCollection->addFieldToFilter('previous_order_expiry_date', array('from' => $minusOneMonth, 'to' => $currentDate))
            #->addAttributeToSort('previous_order_expiry_date', 'ASC'); 


            #echo $productCollection->getSelect()->__toString();exit;

            if($productCollection->getSize()){


                foreach ($productCollection as $item) {
                    $date=$item->getPreviousOrderExpiryDate();

                    $date123 = date("F j, Y",strtotime($date));
                    $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
                    $qty = $StockState->getStockQty($item->getId(), $item->getStore()->getWebsiteId());

                    $itemData = [];
                    $itemData[] = $item->getSku();
                    $itemData[] = $item->getName();
                    $itemData[] = $item->getPreviousOrderCostPrice();
                    $itemData[] = $item->getPreviousOrderExpiryDate();
                    $itemData[] = $item->getPrice();
                    $itemData[] = $item->getStatus();
                    $itemData[] = $item->getResource()->getAttribute('store_location')->getFrontend()->getValue($item);
                    $itemData[] = $item->getResource()->getAttribute('supplier')->getFrontend()->getValue($item);
                    $itemData[] = $qty;
                    $itemData[] = (int)$item->getPreviousOrderCostPrice()*$qty;
                    $stream->writeCsv($itemData);

                }


            }

            # print_r($itemData);exit;
            try{
                $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
                $myfile = $storeManager->getStore()->getBaseUrl()."var/".$filepath;
                $body = "
                <div>
                <p>Report of Inventory from $minusOneMonth to $currentDate </p>
                <p><b>Click here to donload Report</b><a style='background: #59b210;padding: 5px 10px;color: #fff;margin-left: 10px;text-decoration: none;'href=".$myfile.">Download File</a></p>
                </div>";
                $to = ['er.bharatmali@gmail.com'];
                // $email = new \Zend_Mail();
                // $email->setSubject("Report of Inventory");
                // $email->setBodyHtml($body);
                // $email->setFrom($senderEmail, $senderName);
                // $email->addTo($sellerEmail, $sellername);
               # $email->send();

                /*email send*/
                 $templateVars = [
            'minusOneMonth' => $minusOneMonth,
            'currentDate' => $currentDate,
            'myfile' => $myfile
        ];

        $storeId = 1;
        $templateId = '10';

        $sender = [
            'name' => $senderName,
            'email' => $senderEmail
        ];

        $recipient = [
            'name' => $sellername,
            'email' => $sellerEmail
        ];

        $this->inlineTranslation->suspend();

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId,
            ])
            ->setTemplateVars($templateVars)
            ->setFrom($sender)
            ->addTo($recipient['email'], $recipient['name'])
            ->getTransport();

            $transport->sendMessage();
            } catch (\Exception $e) {
                $this->inlineTranslation->resume();
                $this->messageManager->addError(
                    __('We can\'t process your request right now. Sorry, that\'s all we know.'.$e->getMessage())
                );
                return $this;

            }


        }

        $name = date('m_d_y')."_All";

        $this->directory->create('export');
        $filepath = 'export/inventory_report/inventory_report_' . $name . '.csv';
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        $stream->writeCsv($header);


        $currentDate = $date = $this->date->gmtDate('Y-m-d');
        #$days = $this->scopeConfig->getValue(self::XML_PATH_EXPIRE_DAYS, $storeScope)?$this->scopeConfig->getValue(self::XML_PATH_EXPIRE_DAYS, $storeScope): "45";
        # $plusOneMonth = date('Y-m-d', strtotime($currentDate . "90 days"));
        #$minusOneMonth = date('Y-m-d', strtotime($currentDate . "-$days days"));
        $productCollection1 = $this->collectionFactory->create();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $productCollection1->addAttributeToSelect('*');
        #$productCollection-> addAttributeToFilter('store_location', ['neq' => null]);
        # $productCollection1->addFieldToFilter('previous_order_expiry_date', array('from' => $currentDate, 'to' => $plusOneMonth))
        #->addAttributeToSort('store_location', 'ASC'); 

        if($productCollection1->getSize()){

            foreach ($productCollection1 as $item) {
                $date=$item->getPreviousOrderExpiryDate();

                $date123 = date("F j, Y",strtotime($date));
                $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
                $qty = $StockState->getStockQty($item->getId(), $item->getStore()->getWebsiteId());
                $itemData = [];

                $attribute = $item->getResource()->getAttribute('store_location');
                $storel = $attribute->getSource()->getOptionText($item->getStoreLocation());
                $itemData = [];
                $itemData[] = $item->getSku();
                $itemData[] = $item->getName();
                $itemData[] = $item->getPreviousOrderCostPrice();
                $itemData[] = $item->getPreviousOrderExpiryDate();
                $itemData[] = $item->getPrice();
                $itemData[] = $item->getStatus();
                $itemData[] = $item->getResource()->getAttribute('store_location')->getFrontend()->getValue($item);
                $itemData[] = $item->getResource()->getAttribute('supplier')->getFrontend()->getValue($item);
                $itemData[] = $qty;
                $itemData[] =(int)$item->getPreviousOrderCostPrice()*$qty;
                $stream->writeCsv($itemData);




            }

        }
        try {
            // $emailTo = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SEND_TO, $storeScope);
            $emailTo = "naveen_arja@yahoo.co.uk";
            //$emailTo = "er.bharatmali@gmail.com";
            $myfile = $storeManager->getStore()->getBaseUrl()."var/".$filepath;
            $body = "
            <div>
            <p>Report of Inventory from $minusOneMonth to $currentDate </p>
            <p><b>Click here to donload Report</b><a style='background: #59b210;padding: 5px 10px;color: #fff;margin-left: 10px;text-decoration: none;'href=".$myfile.">Download File</a></p>
            </div>";
            // $email = new \Zend_Mail();
            // $email->setSubject("Report of Inventory");
            // $email->setBodyHtml($body);
            // $email->setFrom($senderEmail, $senderName);
            // $email->addTo($emailTo, "Admin");
            // #$email->send();

                  $templateVars = [
            'minusOneMonth' => $minusOneMonth,
            'currentDate' => $currentDate,
            'myfile' => $myfile
        ];

        $storeId = 1;
        $templateId = '10';

        $sender = [
            'name' => $senderName,
            'email' => $senderEmail
        ];

        $recipient = [
            'name' => 'Admin',
            'email' => $emailTo
        ];

        $this->inlineTranslation->suspend();

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId,
            ])
            ->setTemplateVars($templateVars)
            ->setFrom($sender)
            ->addTo($recipient['email'], $recipient['name'])
            ->getTransport();

            $transport->sendMessage();

        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addError(
                __('We can\'t process your request right now. Sorry, that\'s all we know.'.$e->getMessage())
            );
            return $this;

        }

        /*if($sellerCollection->getSize()){
        foreach($sellerCollection as $seller){

        $customerName = $this->customerRepository->getById($seller->getSellerId())->getFirstname();

        // if($seller->getSellerId() == "219"){
        # echo $seller->getSellerId();exit;
        $querydata = $this->_mpProductCollectionFactory->create()->
        addFieldToFilter('seller_id', $seller->getSellerId())
        ->addFieldToFilter('status', ['neq' => SellerProduct::STATUS_DISABLED])
        ->addFieldToSelect('mageproduct_id')
        ->setOrder('mageproduct_id');

        #print_r($querydata->getData());

        $sellerproductCollection =$this->collectionFactory->create();
        $sellerproductCollection->addAttributeToSelect('*');
        $sellerproductCollection->addAttributeToFilter('entity_id', ['in' => $querydata->getData()]);
        $sellerproductCollection->addAttributeToFilter('status', ['neq' => SellerProduct::STATUS_DISABLED]);
        $sellerproductCollection->addFieldToFilter('previous_order_expiry_date', array('from' => $currentDate, 'to' => $plusOneMonth))
        ->addAttributeToSort('previous_order_expiry_date', 'ASC');
        $sellerproductCollection->addStoreFilter();

        # echo $sellerproductCollection->getSelect()->__toString();exit;
        if($sellerproductCollection->getSize()){

        $name = date('m_d_y').$customerName;
        $filepath = 'export/seller/product_' . $name . '.csv';
        $this->directory->create('export');
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        $columns = $this->getColumnHeader();
        foreach ($columns as $column) {
        $header[] = $column;
        }
        $stream->writeCsv($header);

        foreach ($sellerproductCollection as $item) {
        $date=$item->getPreviousOrderExpiryDate();

        $date123 = date("F j, Y",strtotime($date));
        $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
        $qty = $StockState->getStockQty($item->getId(), $item->getStore()->getWebsiteId());

        $itemData = [];
        $itemData[] = $customerName;
        $itemData[] = $item->getEntityId();
        $itemData[] = $item->getSku();
        $itemData[] = $item->getName();
        $itemData[] = $item->getPreviousOrderCostPrice();
        $itemData[] = $item->getPrice();
        $itemData[] = $item->getSpecialPrice();
        $itemData[] = $item->getSpecialFromDate();
        $itemData[] = $item->getSpecialToDate();
        $itemData[] =$date123;
        $itemData[] =$qty;
        $stream->writeCsv($itemData);


        }


        /*$content['type'] = 'filename';
        $content['value'] = $filepath;
        $content['rm'] = 1;
        $this->fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
        # $this->exportToCsv($sellerproductCollection,$seller->getId());
        }//}


        }
        //}
        }*/
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

        #echo $minusOneMonth;exit;
        $productCollection->addFieldToFilter('previous_order_expiry_date', array('from' => $minusOneMonth, 'to' => $plusOneMonth))
        # $productCollection->addFieldToFilter('previous_order_expiry_date', array('lteq' => $plusOneMonth))
        ->addAttributeToSort('previous_order_expiry_date', 'ASC')
        ->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

        #echo $productCollection->getSelect()->__toString();exit;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $emailSender = trim($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope));
        $senderEmail = $this->scopeConfig->getValue('trans_email/ident_'.$emailSender.'/email');
        $senderName = $this->scopeConfig->getValue('trans_email/ident_'.$emailSender.'/name');
        $emailTo = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SEND_TO, $storeScope);
        #$emailTo = "testingdevcheck@gmail.com";
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
            if($qty > 0){
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
            return $this;

        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addError(
                __('We can\'t process your request right now. Sorry, that\'s all we know.'.$e->getMessage())
            );
            return $this;

        }
    }
    public function sendSalesReport(){
        mail("er.bharatmali@gmail.com","sendSalesReport","test");

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');

        $emailSender = trim($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope));
        $senderEmail = $this->scopeConfig->getValue('trans_email/ident_'.$emailSender.'/email');
        $senderName = $this->scopeConfig->getValue('trans_email/ident_'.$emailSender.'/name');


        //$currentDate = $date = $this->date->gmtDate('Y-m-d');

        date_default_timezone_set('Australia/Melbourne');
        $currentDate = date('Y-m-d', time());
        // echo $date;exit;


        $minusOneMonth = date('Y-m-d', strtotime($currentDate . "-1 month"));


        $orderCollection = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\CollectionFactory')->create()
        ->addFieldToSelect(array('*'))
        ->addFieldToFilter('created_at', array('gteq' => $minusOneMonth))
        ->addFieldToFilter('created_at', array('lteq' => $currentDate))
        ->addFieldToFilter('status', array('neq' => "canceled"));

        $columns = $this->getColumnHeaderForSalesReport();
        foreach ($columns as $column) {
            $header[] = $column;
        }

        $name = "salesReport_".$currentDate;

        $this->directory->create('export');
        $filepath = 'export/reports/' . $name.".csv";
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        $stream->writeCsv($header);
        foreach($orderCollection as $order){
            $billingAddress = $order->getBillingAddress();
            $shippingAddress = $order->getShippingAddress();
            $payment = $order->getPayment();
            $method = $payment->getMethodInstance();

            $this->_customfactory = $objectManager->get("Webkul\MobikulCore\Model\OrderPurchasePointFactory")->create()->getCollection();
            $this->_customfactory->addFieldToFilter('increment_id', $order->getData('increment_id'));
            $data = $this->_customfactory->getFirstItem();
            $itemData = [];


            $itemData[] = $order->getData('increment_id');
            $itemData[] = $order->getData('store_name');
            $itemData[] = $order->getData('created_at');;
            $itemData[] = $billingAddress->getFirstname()." ".$billingAddress->getLastname();
            $itemData[] =  $order->getData('customer_firstname')." ". $order->getData('customer_lastname');
            $itemData[] = $order->getData('grand_total');
            $itemData[] = $order->getData('status');
            $itemData[] = $shippingAddress?$shippingAddress->getData('street').",".$shippingAddress->getData('region').",".$shippingAddress->getData('postcode'):"";
            $itemData[] = $order->getData('shipping_description');
            $itemData[] = $order->getData('customer_email');
            $itemData[] = $order->getData('shipping_amount');
            $itemData[] = $method->getTitle();
            $itemData[] = $order->getData('tax_amount');
            $itemData[] = $shippingAddress?$shippingAddress->getData('telephone'):"";
            $itemData[] =$data->getPurchasePoint();

            $stream->writeCsv($itemData);

        }

        try {
            // $emailTo = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SEND_TO, $storeScope);
            $emailTo = "naveen_arja@yahoo.co.uk";
            //$emailTo = "er.bharatmali@gmail.com";
            $myfile = $storeManager->getStore()->getBaseUrl()."var/".$filepath;
            $body = "
            <div>
            <p>Report of Sales orders value from $minusOneMonth to $currentDate </p>
            <p><b>Click here to download Report</b><a style='background: #59b210;padding: 5px 10px;color: #fff;margin-left: 10px;text-decoration: none;'href=".$myfile.">Download File</a></p>
            </div>";
            // $email = new \Zend_Mail();
            // $email->setSubject("Report of Sales orders value");
            // $email->setBodyHtml($body);
            // $email->setFrom($senderEmail, $senderName);
            // $email->addTo($emailTo, "Admin");
            // $email->send();

             $templateVars = [
            'minusOneMonth' => $minusOneMonth,
            'currentDate' => $currentDate,
            'myfile' => $myfile
        ];

        $storeId = 1;
        $templateId = '8';

        $sender = [
            'name' => $senderName,
            'email' => $senderEmail
        ];

        $recipient = [
            'name' => "Admin",
            'email' => $emailTo
        ];

        $this->inlineTranslation->suspend();

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId,
            ])
            ->setTemplateVars($templateVars)
            ->setFrom($sender)
            ->addTo($recipient['email'], $recipient['name'])
            ->getTransport();

        $transport->sendMessage();
echo "test2";
        $this->inlineTranslation->resume();

        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addError(
                __('We can\'t process your request right now. Sorry, that\'s all we know.'.$e->getMessage())
            );
            return $this;

        }

    }

    public function sendSalesReportcustom(){
        #mail("er.bharatmali@gmail.com","sendSalesReport","test");


        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');

        $emailSender = trim($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope));
        $senderEmail = $this->scopeConfig->getValue('trans_email/ident_'.$emailSender.'/email');
        $senderName = $this->scopeConfig->getValue('trans_email/ident_'.$emailSender.'/name');


        //$currentDate = $date = $this->date->gmtDate('Y-m-d');

        date_default_timezone_set('Australia/Melbourne');
        $currentDate = date('Y-m-d H:i:s', time());
        // echo $date;exit;


        // $minusOneMonth = date('Y-m-d', strtotime($currentDate . "-1 month"));
        //$days = $this->scopeConfig->getValue(self::XML_PATH_EXPIRE_DAYS, $storeScope)?$this->scopeConfig->getValue(self::XML_PATH_EXPIRE_DAYS, $storeScope): "45";


        /*  $orderCollection = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\CollectionFactory')->create()
        ->addFieldToSelect(array('*'))
        ->addFieldToFilter('created_at', array('gteq' => "2021-01-01 H:i:s"))
        # ->addFieldToFilter('created_at', array('lteq' => "2020-12-31 H:i:s"))
        ->addFieldToFilter('status', array('neq' => "canceled"));
        */

        $orderCollection = $objectManager->create('Magento\Customer\Model\CustomerFactory')->create()->getCollection()
        ->addAttributeToSelect("*")
        #->addAttributeToFilter('created_at', array('gteq' => "2021-01-01 H:i:s"))
        #  ->addAttributeToFilter('created_at', array('lteq' => "2020-12-31 H:i:s"))
        ->load();

        #echo count($orderCollection);exit;
        $columns = $this->getColumnHeaderForSalesReport();
        foreach ($columns as $column) {
            $header[] = $column;
        }

        $name = "customers_6month";

        $this->directory->create('export');
        $filepath = 'export/reports/' . $name.".csv";
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        $stream->writeCsv($header);
        $customerIdArray=array();
        foreach($orderCollection as $order){

            $customerId = $order->getEntityId();
            $orderCollections = $objectManager->create("Magento\Sales\Model\Order")->getCollection()
            ->addFieldToFilter('customer_id', $customerId);

            if($orderCollections->count() == 1){
                //$customerIdArray[]=$customerId;
                # echo ($customerId)."asdsadsa";

                $createdDate = $orderCollections->getFirstItem()->getCreatedAt();
                #echo $createdDate;exit;

                # echo $currentDate."--------------- ".$createdDate;exit;  
                //if() 
                $threeMonth = date('Y-m-d H:i:s', strtotime($currentDate . "-90 days"));
                $sixMonth = date('Y-m-d H:i:s', strtotime($currentDate . "-180 days"));
                $oneyear = date('Y-m-d H:i:s', strtotime($currentDate . "-365 days"));

                # echo $threeMonth;exit;
                $orderCollections->addAttributeToFilter('created_at', array('gteq' => $sixMonth));

                #  $orderCollection->addFieldToFilter(['created_at','created_at','created_at'],[['lteq'=>$threeMonth],['lteq'=>$sixMonth],['lteq'=>$oneyear]]);
                # $orderCollection->addFieldToFilter(['created_at'],[['gteq'=>$threeMonth]]);
                //foreach($orderCollections as $customer){
                #echo count($orderCollections);exit;
                # echo $customerId."asdsad".$orderCollections->count();exit;
                if($orderCollections->count() == 1){
                    #echo $orderCollections->getSelect()->__toString();exit;

                    $customerIdArray[]=$customerId;
                    #echo $createdDate;exit;

                    $shippingAddressId = $order->getDefaultShipping();
                    $addressCollectiion = $objectManager->get('Magento\Customer\Model\AddressFactory')->create()->load($shippingAddressId);
                    #echo $addressCollectiion->gePostcode();exit;
                    #$order->getDefaultShipping();

                    $itemData = [];


                    $itemData[] = $order->getFirstname()." ".$order->getLastname();
                    $itemData[] = $order->getEmail();
                    $itemData[] = $addressCollectiion->getTelephone();
                    $itemData[] = $order->getShippingCode();
                    $itemData[] = $createdDate;

                    #$itemData[] = $billingAddress->getFirstname()." ".$billingAddress->getLastname();


                    $itemData[] = $addressCollectiion->getStreet()[0]?$addressCollectiion->getStreet()[0].",".$addressCollectiion->getregion().",".$addressCollectiion->getPostcode():"";


                    #print_R($itemData);exit;
                    $stream->writeCsv($itemData);  
                }
                //  }
                //if($orderCollection->count() > 0){

                //}

            }


        }
        echo "ok";exit;
        #echo implode(",",$customerIdArray);

        /* foreach($customerIdArray as $c){
        // $objectManager->create('Magento\Customer\Model\Customer')->load($c);

        $createdDate = $orderCollections->getFirstItem()->getcreated_at();
        # echo $currentDate."--------------- ".$createdDate;exit;  
        //if() 
        $threeMonth = date('Y-m-d H:i:s', strtotime($currentDate . "-90 days"));
        $sixMonth = date('Y-m-d H:i:s', strtotime($currentDate . "-180 days"));
        $oneyear = date('Y-m-d H:i:s', strtotime($currentDate . "-368 days"));

        #echo $threeMonth;exit;
        $orderCollections->addAttributeToFilter('created_at', array('gteq' => $threeMonth));


        $shippingAddressId = $order->getDefaultShipping();
        $addressCollectiion = $objectManager->get('Magento\Customer\Model\AddressFactory')->create()->load($shippingAddressId);
        #echo $addressCollectiion->gePostcode();exit;
        #$order->getDefaultShipping();

        $itemData = [];


        $itemData[] = $order->getFirstname()." ".$order->getLastname();
        $itemData[] = $order->getEmail();
        $itemData[] = $addressCollectiion->getTelephone();
        $itemData[] = $order->getShippingCode();
        $itemData[] = $createdDate;

        #$itemData[] = $billingAddress->getFirstname()." ".$billingAddress->getLastname();


        $itemData[] = $addressCollectiion->getStreet()[0]?$addressCollectiion->getStreet()[0].",".$addressCollectiion->getregion().",".$addressCollectiion->getPostcode():"";


        #print_R($itemData);exit;
        $stream->writeCsv($itemData);
        }*/


        #    echo count($customerIdArray);exit;



    }
    public function sendSalesReport1(){
        # mail("er.bharatmali@gmail.com","sendSalesReport","test");

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');

        $emailSender = trim($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope));
        $senderEmail = $this->scopeConfig->getValue('trans_email/ident_'.$emailSender.'/email');
        $senderName = $this->scopeConfig->getValue('trans_email/ident_'.$emailSender.'/name');


        $currentDate = $date = $this->date->gmtDate('Y-m-d h:i:s');
        #   ECHO $currentDate;EXIT;
        $minusOneMonth = date('Y-m-d', strtotime($currentDate . "-1 month"));
        $orderCollection = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\CollectionFactory')->create()
        ->addFieldToSelect(array('*'))
        # ->addFieldToFilter('created_at', array('gteq' => "2018-12-22 01:58:20"))
        #  ->addFieldToFilter('created_at', array('lteq' => "2020-12-27 00:00:00"));
        ->addFieldToFilter('created_at', array('gt' => "2022-01-01 00:00:00"))
        ->addFieldToFilter('created_at', array('lteq' => "2022-02-28 00:00:00"));
        //->addFieldToFilter('created_at', array('lteq' =>$currentDate));

        #echo count($orderCollection);exit;
        $columns = $this->getColumnHeaderForSalesReport();
        foreach ($columns as $column) {
            $header[] = $column;
        }

        $name = "salesReportFrom_jan2022to_feb2022";

        $this->directory->create('export');
        $filepath = 'export/reports/' . $name.".csv";
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        $stream->writeCsv($header);
        foreach($orderCollection as $order){
            $billingAddress = $order->getBillingAddress();
            $shippingAddress = $order->getShippingAddress();
            $payment = $order->getPayment();
            $method = $payment->getMethodInstance();

            $this->_customfactory = $objectManager->get("Webkul\MobikulCore\Model\OrderPurchasePointFactory")->create()->getCollection();
            $this->_customfactory->addFieldToFilter('increment_id', $order->getData('increment_id'));
            $data = $this->_customfactory->getFirstItem();
            $itemData = [];


            $itemData[] = $order->getData('increment_id');
            $itemData[] = $order->getData('store_name');
            $itemData[] = $order->getData('created_at');;
            $itemData[] = $billingAddress->getFirstname()." ".$billingAddress->getLastname();
            $itemData[] =  $order->getData('customer_firstname')." ". $order->getData('customer_lastname');
            $itemData[] = $order->getData('grand_total');
            $itemData[] = $order->getData('status');
            $itemData[] = $shippingAddress?$shippingAddress->getData('street').",".$shippingAddress->getData('region').",".$shippingAddress->getData('postcode'):"";
            $itemData[] = $order->getData('shipping_description');
            $itemData[] = $order->getData('customer_email');
            $itemData[] = $order->getData('shipping_amount');
            $itemData[] = $method->getTitle();
            $itemData[] = $order->getData('tax_amount');
            $itemData[] = $shippingAddress?$shippingAddress->getData('telephone'):"";
            $itemData[] =$data->getPurchasePoint();

            $stream->writeCsv($itemData);

        }

        try {
            // $emailTo = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SEND_TO, $storeScope);
            #    $emailTo = "naveen_arja@yahoo.co.uk";
            $emailTo = "er.bharatmali@gmail.com";
            $myfile = $storeManager->getStore()->getBaseUrl()."var/".$filepath;
            $body = "
            <div>
            <p>Report of Sales orders value from $minusOneMonth to $currentDate </p>
            <p><b>Click here to download Report</b><a style='background: #59b210;padding: 5px 10px;color: #fff;margin-left: 10px;text-decoration: none;'href=".$myfile.">Download File</a></p>
            </div>";
            // $email = new \Zend_Mail();
            // $email->setSubject("Report of Sales orders value");
            // $email->setBodyHtml($body);
            // $email->setFrom($senderEmail, $senderName);
            // $email->addTo($emailTo, "Admin");
            // $email->send();

            /*email send*/

             $templateVars = [
            'minusOneMonth' => $minusOneMonth,
            'currentDate' => $currentDate,
            'myfile' => $myfile
        ];

        $storeId = 1;
        $templateId = '8';

        $sender = [
            'name' => $senderName,
            'email' => $senderEmail
        ];

        $recipient = [
            'name' => "Admin",
            'email' => $emailTo
        ];

        $this->inlineTranslation->suspend();

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId,
            ])
            ->setTemplateVars($templateVars)
            ->setFrom($sender)
            ->addTo($recipient['email'], $recipient['name'])
            ->getTransport();

        $transport->sendMessage();

        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addError(
                __('We can\'t process your request right now. Sorry, that\'s all we know.'.$e->getMessage())
            );
            return $this;

        }

    }
    public function sendSalesReportToSellers(){
        mail("er.bharatmali@gmail.com","sendSalesReportToSellers    ","test");
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');

        $emailSender = trim($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope));
        $senderEmail = $this->scopeConfig->getValue('trans_email/ident_'.$emailSender.'/email');
        $senderName = $this->scopeConfig->getValue('trans_email/ident_'.$emailSender.'/name');


        date_default_timezone_set('Australia/Melbourne');
        $currentDate = date('Y-m-d', time());

        //  $currentDate = $date = $this->date->gmtDate('Y-m-d');
        $minusOneMonth = date('Y-m-d', strtotime($currentDate . "-1 month"));
        $orderCollection = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\CollectionFactory')->create()
        ->addFieldToSelect(array('*'))
        ->addFieldToFilter('created_at', array('gteq' => $minusOneMonth))
        ->addFieldToFilter('created_at', array('lteq' => $currentDate));

        $storeLocation = array("219","1189","6677");
        $columns = $this->getColumnHeaderForSalesReport();
        foreach ($columns as $column) {
            $header[] = $column;
        }


        foreach($storeLocation as $seller){
            /* if($seller == "219"){
            $orderPrfix = null;
            }*/

            if($seller == 219){
                $customerName = "3024";
                $sellerEmail = "routificnineteens@gmail.com";
                #$sellerEmail = "testingdevcheck@gmail.com";
                $sellername = "Jaydeep Arja";
            }
            if($seller == 1189){
                $customerName = "3030";
                #$sellerEmail = "er.bharatmali@gmail.com";
                $sellerEmail = "srisairam@superbazaar.com.au";
                $sellername = "Mr Sai Balaji SuperBazaar WS";
            }
            if($seller == 6677){
                $customerName = "3064";
                #$sellerEmail = "sharda0728@gmail.com";
                $sellerEmail = "admin3064@superbazaar.com.au";
                $sellername = "Superbazaar 3064";
            }

            $name = date('m_d_y')."_salesReport_".$customerName;


            $this->directory->create('export');
            $filepath = 'export/reports/' . $name.".csv";
            $stream = $this->directory->openFile($filepath, 'w+');
            $stream->lock();

            $stream->writeCsv($header);

            $Sellercollection = $objectManager->get('Webkul\MpHyperLocal\Model\ShipAreaFactory')->create()
            ->getCollection()
            ->addFieldToFilter('seller_id', $seller);
            $postCodes = $Sellercollection->getColumnValues('postcode');
            $postCodes= array_map('trim', $postCodes);

            foreach($orderCollection as $order){
                $billingAddress = $order->getBillingAddress();
                $shippingAddress = $order->getShippingAddress();
                if($shippingAddress && in_array($shippingAddress->getData('postcode'),$postCodes)){
                    $payment = $order->getPayment();
                    $method = $payment->getMethodInstance();

                    $this->_customfactory = $objectManager->get("Webkul\MobikulCore\Model\OrderPurchasePointFactory")->create()->getCollection();
                    $this->_customfactory->addFieldToFilter('increment_id', $order->getData('increment_id'));
                    $data = $this->_customfactory->getFirstItem();
                    $itemData = [];


                    $itemData[] = $order->getData('increment_id');
                    $itemData[] = $order->getData('store_name');
                    $itemData[] = $order->getData('created_at');;
                    $itemData[] = $billingAddress->getFirstname()." ".$billingAddress->getLastname();
                    $itemData[] =  $order->getData('customer_firstname')." ". $order->getData('customer_lastname');
                    $itemData[] = $order->getData('grand_total');
                    $itemData[] = $order->getData('status');
                    $itemData[] = $shippingAddress?$shippingAddress->getData('street').",".$shippingAddress->getData('region').",".$shippingAddress->getData('postcode'):"";
                    $itemData[] = $order->getData('shipping_description');
                    $itemData[] = $order->getData('customer_email');
                    $itemData[] = $order->getData('shipping_amount');
                    $itemData[] = $method->getTitle();
                    $itemData[] = $order->getData('tax_amount');
                    $itemData[] = $shippingAddress?$shippingAddress->getData('telephone'):"";
                    $itemData[] =$data->getPurchasePoint();

                    $stream->writeCsv($itemData);

                }
            }

            try {
                // $emailTo = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SEND_TO, $storeScope);
                $emailTo = "naveen_arja@yahoo.co.uk";
                #$emailTo = "er.bharatmali@gmail.com";
                $myfile = $storeManager->getStore()->getBaseUrl()."var/".$filepath;
                $body = "
                <div>
                <p>Report of Sales orders value from $minusOneMonth to $currentDate </p>
                <p><b>Click here to download Report</b><a style='background: #59b210;padding: 5px 10px;color: #fff;margin-left: 10px;text-decoration: none;'href=".$myfile.">Download File</a></p>
                </div>";
                // $email = new \Zend_Mail();
                // $email->setSubject("Report of Sales orders value");
                // $email->setBodyHtml($body);
                // $email->setFrom($senderEmail, $senderName);
                // $email->addTo($sellerEmail, $sellername);
                // $email->send();

                /*email send*/
                 $templateVars = [
            'minusOneMonth' => $minusOneMonth,
            'currentDate' => $currentDate,
            'myfile' => $myfile
        ];

        $storeId = 1;
        $templateId = '8';

        $sender = [
            'name' => $senderName,
            'email' => $senderEmail
        ];

        $recipient = [
            'name' => "Admin",
            'email' => $emailTo
        ];

        $this->inlineTranslation->suspend();

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId,
            ])
            ->setTemplateVars($templateVars)
            ->setFrom($sender)
            ->addTo($recipient['email'], $recipient['name'])
            ->getTransport();

        $transport->sendMessage();

            } catch (\Exception $e) {
                $this->inlineTranslation->resume();
                $this->messageManager->addError(
                    __('We can\'t process your request right now. Sorry, that\'s all we know.'.$e->getMessage())
                );
                return $this;

            }
        }
    }


    public function sendSalesReportToSellersProduct(){
		
		#echo "asdsa";exit;
        #  mail("er.bharatmali@gmail.com","sendSalesReportToSellers    ","test");
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');

        $emailSender = trim($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope));
        $senderEmail = $this->scopeConfig->getValue('trans_email/ident_'.$emailSender.'/email');
        $senderName = $this->scopeConfig->getValue('trans_email/ident_'.$emailSender.'/name');


        date_default_timezone_set('Australia/Melbourne');
        $currentDate = date('Y-m-d', time());

        //  $currentDate = $date = $this->date->gmtDate('Y-m-d');
        $minusOneMonth = date('Y-m-d', strtotime($currentDate . "-1 month"));
        $orderCollection = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\CollectionFactory')->create()
        ->addFieldToSelect(array('*'))
        ->addFieldToFilter('created_at', array('gteq' => "2023-05-01 H:i:s"))
        ->addFieldToFilter('created_at', array('lteq' => "2023-05-31 H:i:s"));
		   #echo ($orderCollection->getSelect()->__toString());exit;


       # $storeLocation = array("6637","1189","6677");
	   /* for seperate seller report */
       # $storeLocation = array("6637"); // for 3024 seller
        #$storeLocation = array("1189"); // for 3030 seller
        $storeLocation = array("6677");// for 3064 seller
		/* for seperate seller report */
        $columns = $this->getColumnHeaderForSalesReport2();
        foreach ($columns as $column) {
            $header[] = $column;
        }


        foreach($storeLocation as $seller){
       
            if($seller == 6637){
                $customerName = "3024";
                $sellerEmail = "routificnineteens@gmail.com";
                #$sellerEmail = "testingdevcheck@gmail.com";
                $sellername = "Jaydeep Arja";
            }
            if($seller == 1189){
                $customerName = "3030";
                #$sellerEmail = "er.bharatmali@gmail.com";
                $sellerEmail = "srisairam@superbazaar.com.au";
                $sellername = "Mr Sai Balaji SuperBazaar WS";
            }
            if($seller == 6677){
                $customerName = "3064";
                #$sellerEmail = "sharda0728@gmail.com";
                $sellerEmail = "admin3064@superbazaar.com.au";
                $sellername = "Superbazaar 3064";
            }

            $name = date('m_d_y')."_salesReport_".$customerName;


            $this->directory->create('export');
            $filepath = 'export/reports/sales/' . $name.".csv";
            $stream = $this->directory->openFile($filepath, 'w+');
            $stream->lock();

            $stream->writeCsv($header);

            $Sellercollection = $objectManager->get('Webkul\MpHyperLocal\Model\ShipAreaFactory')->create()
            ->getCollection()
            #addFieldToFilter('seller_id',array('null' => true)); /* for 3024 seller */
            ->addFieldToFilter('postcode',3064); /* for 3030 and 3064 seller */
			 #->addFieldToFilter('seller_id', 6637);


            $postCodes = $Sellercollection->getColumnValues('postcode');
			/* for 3024 postcode order */
			 if($seller == 6637){
				array_push($postCodes,"3024","3029");
			 }
			/* for 3024 postcode order */
			/* for 3024 postcode order */
			 /*if($seller == 6677){
				array_splice($postCodes,"3024","3029");
			 }*/
			/* for 3024 postcode order */

            # print_r($postCodes);exit;
            $postCodes= array_map('trim', $postCodes);
			#print_r($postCodes);exit;

           # print_r($postCodes);exit;
            foreach($orderCollection as $order){
                $billingAddress = $order->getBillingAddress();
                $shippingAddress = $order->getShippingAddress();
                if($shippingAddress && in_array($shippingAddress->getData('postcode'),$postCodes)){
                    $payment = $order->getPayment();
                    $method = $payment->getMethodInstance();

                    $this->_customfactory = $objectManager->get("Webkul\MobikulCore\Model\OrderPurchasePointFactory")->create()->getCollection();
                    $this->_customfactory->addFieldToFilter('increment_id', $order->getData('increment_id'));
                    $data = $this->_customfactory->getFirstItem();
                    $itemData = [];


                    $itemData[] = $order->getData('increment_id');
                    $itemData[] = $order->getData('store_name');
                    $itemData[] = $order->getData('created_at');;
                    $itemData[] = $billingAddress->getFirstname()." ".$billingAddress->getLastname();
                    $itemData[] =  $order->getData('customer_firstname')." ". $order->getData('customer_lastname');
                    $itemData[] = $order->getData('grand_total');
                    $itemData[] = $order->getData('status');
                    $itemData[] = $shippingAddress?$shippingAddress->getData('street').",".$shippingAddress->getData('region').",".$shippingAddress->getData('postcode'):"";
                    $itemData[] = $order->getData('shipping_description');
                    $itemData[] = $order->getData('customer_email');
                    $itemData[] = $order->getData('shipping_amount');
                    $itemData[] =  $method->getTitle();
                    $itemData[] =  $order->getData('payment_fee');
                    $itemData[] = $shippingAddress->getData('postcode');
                    $itemData[] = $order->getData('tax_amount');
                    $itemData[] = $shippingAddress?$shippingAddress->getData('telephone'):"";
                    $itemData[] =$data->getPurchasePoint();

                    $stream->writeCsv($itemData);

                }
            }

           /* try {
            // $emailTo = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SEND_TO, $storeScope);
            $emailTo = "naveen_arja@yahoo.co.uk";
            #$emailTo = "er.bharatmali@gmail.com";
            $myfile = $storeManager->getStore()->getBaseUrl()."var/".$filepath;
            $body = "
            <div>
            <p>Report of Sales orders value from $minusOneMonth to $currentDate </p>
            <p><b>Click here to download Report</b><a style='background: #59b210;padding: 5px 10px;color: #fff;margin-left: 10px;text-decoration: none;'href=".$myfile.">Download File</a></p>
            </div>";
            $email = new \Zend_Mail();
            $email->setSubject("Report of Sales orders value");
            $email->setBodyHtml($body);
            $email->setFrom($senderEmail, $senderName);
            $email->addTo($sellerEmail, $sellername);
            $email->send();

            } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addError(
            __('We can\'t process your request right now. Sorry, that\'s all we know.'.$e->getMessage())
            );
            return $this;

            }      */  
        }
    }

    public function getAllProduct(){
        # mail("er.bharatmali@gmail.com","cron run","test");
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $sellerCollection = $this->_sellerCollectionFactory->create();
        // $sellerCollection->addAttributeToFilter('seller_id', ['in' => $querydata->getData()]);;
        $currentDate = $date = $this->date->gmtDate('Y-m-d');
        #echo $currentDate;
        $days = $this->scopeConfig->getValue(self::XML_PATH_EXPIRE_DAYS, $storeScope)?$this->scopeConfig->getValue(self::XML_PATH_EXPIRE_DAYS, $storeScope): "45";
        $plusOneMonth = date('Y-m-d', strtotime($currentDate . "90 days"));
        $minusOneMonth = date('Y-m-d', strtotime($currentDate . "-90 days"));
        # echo $plusOneMonth;exit;

        # echo $sellerCollection->getSize();exit;
        $storeLocation = array("1022","6453","7023");
        $csv =[];

        /* Open file */


        /* Write Header */
        $columns = $this->getColumnHeader1();
        foreach ($columns as $column) {
            $header[] = $column;
        }
        foreach($storeLocation as $postcode){

            if($postcode == 1022){
                $customerName = "3024";
                $sellerEmail = "routificnineteens@gmail.com";
                #$sellerEmail = "testingdevcheck@gmail.com";
                $sellername = "Jaydeep Arja";
            }
            if($postcode == 6453){
                $customerName = "3030";
                #$sellerEmail = "er.bharatmali@gmail.com";
                $sellerEmail = "srisairam@superbazaar.com.au";
                $sellername = "Mr Sai Balaji SuperBazaar WS";
            }
            if($postcode == 7023){
                $customerName = "3064";
                # $sellerEmail = "sharda0728@gmail.com";
                $sellerEmail = "admin3064@superbazaar.com.au";
                $sellername = "Superbazaar 3064";
            }

            $name = date('m_d_y')."_".$customerName;

            $this->directory->create('export');
            $filepath = 'export/ALL_product.csv';
            $stream = $this->directory->openFile($filepath, 'w+');
            $stream->lock();

            $stream->writeCsv($header);
            $emailSender = trim($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope));
            $senderEmail = $this->scopeConfig->getValue('trans_email/ident_'.$emailSender.'/email');
            $senderName = $this->scopeConfig->getValue('trans_email/ident_'.$emailSender.'/name');

            $currentDate = $date = $this->date->gmtDate('Y-m-d');
            $days = $this->scopeConfig->getValue(self::XML_PATH_EXPIRE_DAYS, $storeScope)?$this->scopeConfig->getValue(self::XML_PATH_EXPIRE_DAYS, $storeScope): "45";
            $plusOneMonth = date('Y-m-d', strtotime($currentDate . "90 days"));
            $minusOneMonth = date('Y-m-d', strtotime($currentDate . "-90 days"));


            #echo $minusOneMonth;exit;

            $productCollection = $this->collectionFactory->create();
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $productCollection->addAttributeToSelect('*');


            #echo $productCollection->getSelect()->__toString();exit;

            if($productCollection->getSize()){


                foreach ($productCollection as $item) {
                    $date=$item->getPreviousOrderExpiryDate();

                    $date123 = date("F j, Y",strtotime($date));
                    $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
                    $qty = $StockState->getStockQty($item->getId(), $item->getStore()->getWebsiteId());
                    
                    #$stockItem = $objectManager->get('\Magento\CatalogInventory\Model\Stock\StockItemRepository');

                    #$stockStatus = $stockItem->get($item->getId());
                    $itemData = [];
                    $itemData[] = $item->getStatus();
                    $itemData[] = $item->getSku();
                    $itemData[] = $item->getName();
                    $itemData[] = $item->getPreviousOrderCostPrice();
                    $itemData[] =$qty;
                    $itemData[] = $item->isAvailable()?"in stock":"out of stock";
                    $itemData[] = $date123;
                    
                    $stream->writeCsv($itemData);

                }


            }
           

        }

    }
}