<?php

namespace Superbazaar\General\Controller\Adminhtml\Productcsv;
ini_set('max_execution_time', 1200); 

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class CsvStock extends Action
{
    protected $uploaderFactory;
    private $filter;
    private $collectionFactory;
    protected $_locationFactory; 
    protected $stockItem;



    public function __construct(
        Item $stockItem,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $locationFactory,
        Filter $filter,
        CollectionFactory $collectionFactory

    ) {
        parent::__construct($context);
        $this->_fileFactory = $fileFactory;
        $this->_locationFactory = $locationFactory;
        $this->stockItem = $stockItem;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR); // VAR Directory Path
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            
            $collection->addAttributeToSelect('*')
            ->load();
            $name = date('m-d-Y-H-i-s');
            $filepath = 'export/export-data-' .$name. '.csv'; // at Directory path Create a Folder Export and FIle
            $this->directory->create('export');

            $stream = $this->directory->openFile($filepath, 'w+');
            $stream->lock();

            //column name dispay in your CSV 

            $columns = ['sku','name','type','attribute_set_code','price','qty','visibility','status','product_websites','store_location','supplier'];


            foreach ($columns as $column) 
            {
                $header[] = $column; //storecolumn in Header array
            }

            $stream->writeCsv($header);


            #$StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');


            foreach($collection as $item){
                $itemData = [];
                $itemData = [];
                $itemData[] = $item->getData('sku');
                $itemData[] = $item->getData('name');
                $itemData[] = $item->getData('type_id');
                $itemData[] = $item->getData('attribute_set_id');
                $itemData[] = $item->getData('price');
                $itemData[] = $this->stockItem->load($item->getEntityId(), 'product_id')->getQty();
                $itemData[] = $item->getAttributeText('visibility');
                $itemData[] = $item->getData('status');
                $itemData[] = 'Main Website';
                $itemData[] = $item->getAttributeText('store_location');
                $itemData[] = $item->getAttributeText('supplier');
                $stream->writeCsv($itemData);

            }

            $content = [];
            $content['type'] = 'filename'; // must keep filename
            $content['value'] = $filepath;
            $content['rm'] = '1'; //remove csv from var folder

            $csvfilename = 'catalog_product-'.$name.'.csv';
            return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
    * @return bool
    */
    /*protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('{Namespace}_{Module}::mass_{action}');
    }*/
    /*public function execute()
    {
        //csv
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');

        $location = $productCollection->addAttributeToSelect('*')
        ->load();

        $name = date('m-d-Y-H-i-s');
        $filepath = 'export/export-data-' .$name. '.csv'; // at Directory path Create a Folder Export and FIle
        $this->directory->create('export');

        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        //column name dispay in your CSV 

        $columns = ['sku','name','type','attribute_set_code','price','qty','visibility','status','product_websites','store_location','supplier'];


        foreach ($columns as $column) 
        {
            $header[] = $column; //storecolumn in Header array
        }

        $stream->writeCsv($header);


        #$StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');


        foreach($location as $item){
            $itemData = [];
            $itemData[] = $item->getData('sku');
            $itemData[] = $item->getData('name');
            $itemData[] = $item->getData('type_id');
            $itemData[] = $item->getData('attribute_set_id');
            $itemData[] = $item->getData('price');
            $itemData[] = $this->stockItem->load($item->getEntityId(), 'product_id')->getQty();
            $itemData[] = $item->getAttributeText('visibility');
            $itemData[] = $item->getData('status');
            $itemData[] = 'Main Website';
            $itemData[] = $item->getAttributeText('store_location');
            $itemData[] = $item->getAttributeText('supplier');
            $stream->writeCsv($itemData);

        }

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'catalog_product-'.$name.'.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);

    }*/


}