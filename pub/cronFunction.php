<?php
 error_reporting(E_ALL);
 ini_set('display_errors', 1);
use Magento\Framework\App\Bootstrap;
 require '../app/bootstrap.php';
 $bootstrap = Bootstrap::create(BP, $_SERVER);
 $objectManager = $bootstrap->getObjectManager();
 $state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('global');

  // $userFactory = $objectManager->get('\Superbazaar\General\Cron\ProductExpireSend')->sendInventoryReport();
  // $userFactory = $objectManager->get('\Superbazaar\General\Cron\ProductExpireSend')->exportToCsv();
  $userFactory = $objectManager->get('\Superbazaar\General\Cron\ProductExpireSend')->sendSalesReport();
  // $userFactory = $objectManager->get('\Superbazaar\General\Cron\ProductExpireSend')->sendSalesReportcustom();
  // $userFactory = $objectManager->get('\Superbazaar\General\Cron\ProductExpireSend')->sendSalesReport1();
  // $userFactory = $objectManager->get('\Superbazaar\General\Cron\ProductExpireSend')->sendSalesReportToSellers();
  // $userFactory = $objectManager->get('\Superbazaar\General\Cron\ProductExpireSend')->sendSalesReportToSellersProduct();
  // $userFactory = $objectManager->get('\Superbazaar\General\Cron\ProductExpireSend')->getAllProduct();
  
  
  // $userFactory = $objectManager->get('\Superbazaar\General\Cron\ProductExpireSend')->execute();
  if($userFactory == null){
    echo "null";
  }
  $bootstrap->run($userFactory);