<?php

namespace Superbazaar\General\Controller\Adminhtml\Productcsv;
ini_set('max_execution_time', 1200); 

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class gridToCsv extends Action
{
    protected $uploaderFactory;

    protected $_locationFactory; 

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $locationFactory // This is returns Collaction of Data

    ) {
        parent::__construct($context);
        $this->_fileFactory = $fileFactory;
        $this->_locationFactory = $locationFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR); // VAR Directory Path
        parent::__construct($context);
    }
    public function execute()
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

        $columns = ['sku','name','type','attribute_set_code','price','visibility','status','product_websites','store_location','supplier'];

        foreach ($columns as $column) 
        {
            $header[] = $column; //storecolumn in Header array
        }

        $stream->writeCsv($header);


        $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');


        foreach($location as $item){
            $itemData = [];
            $itemData[] = $item->getData('sku');
            $itemData[] = $item->getData('name');
            $itemData[] = $item->getData('type_id');
            $itemData[] = $item->getAttributeText('attribute_set_id');
            $itemData[] = $item->getData('price');
           # $itemData[] = $StockState->getStockQty($item->getEntityId(),1);
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

    }


}