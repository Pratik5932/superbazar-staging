<?php
error_reporting(E_ALL);
 ini_set('display_errors', 1);
use Magento\Framework\App\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Tschallacka\Helper\AttributeHelper;
use Tschallacka\Attributes\Attributes;
use Tschallacka\Attributes\Suppliers;

require __DIR__ . '/../app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('global');

#$userFactory = $objectManager->get('\Superbazaar\General\Cron\ProductExpireSend')->exportToCsv();

$csvarray = array();
if (($handle = fopen('products.csv', 'r')) !== FALSE) { // Check the resource is valid
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) { // Check opening the file is OK!
        //print_r($data); // Array
		$csvarray[]= $data;
    }
    fclose($handle);
}

	
foreach($csvarray as $sku){
	$productRepository = $objectManager->create(ProductRepositoryInterface::class);
    $product = $productRepository->get($sku[0]);
	if($product->getId()){
		$product->setData("store_location","7023");
		$product->setStoreId(0);
		$productRepository->save($product);
	}
}