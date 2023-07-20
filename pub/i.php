<?php
error_reporting(E_ALL);
 ini_set('display_errors', 1);
use Magento\Framework\App\Bootstrap;
  require __DIR__ . '/../app/bootstrap.php';
 $bootstrap = Bootstrap::create(BP, $_SERVER);
 $objectManager = $bootstrap->getObjectManager();
 $state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('global');

 #$userFactory = $objectManager->get('\Superbazaar\General\Cron\ProductExpireSend')->exportToCsv();
  
 #$userFactory = $objectManager->get('\Magento\ProductAlert\Model\Mailing\AlertProcessor')->process("stock",array("9033"),1);
