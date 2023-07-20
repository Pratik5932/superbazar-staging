<?php
/**
* Webkul Software.
*
* @category  Webkul
* @package   Webkul_MpHyperLocal
* @author    Webkul
* @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
* @license   https://store.webkul.com/license.html
*/

namespace Superbazaar\General\Controller\Adminhtml\Index;

class InlineEdit extends \Magento\Backend\App\Action
{

    protected $jsonFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $entityId) {
                    $product = $this->_objectManager->create('\Magento\Catalog\Api\ProductRepositoryInterface')->get($postItems[$entityId]['sku'],true, 0, true);
                    if(isset($postItems[$entityId]['sku'])){
                        $product->setSku($postItems[$entityId]['sku']);
                    }
                    if(isset($postItems[$entityId]['name'])){
                        $product->setData('name',$postItems[$entityId]['name']);

                    }
                    if(isset($postItems[$entityId]['previous_order_cost_price'])){
                        $product->setData('previous_order_cost_price',$postItems[$entityId]['previous_order_cost_price']);
                    }
                    if(isset($postItems[$entityId]['previous_order_expiry_date'])){
                        $product->setData('previous_order_expiry_date',$postItems[$entityId]['previous_order_expiry_date']);
                    }
                    if(isset($postItems[$entityId]['price'])){
                        $product->setPrice($postItems[$entityId]['price']);   
                    }
                    if(isset($postItems[$entityId]['special_price'])){
                        $product->setSpecialPrice($postItems[$entityId]['special_price']);   
                    }
                    if(isset($postItems[$entityId]['special_from_date'])){
                        $product->setSpecialFromDate($postItems[$entityId]['special_from_date']);   
                    }
                    if(isset($postItems[$entityId]['special_to_date'])){
                        $product->setSpecialToDate($postItems[$entityId]['special_to_date']);
                        $product->setSpecialToDateIsFormated(true);   
                    }

                    if(isset($postItems[$entityId]['status'])) {
                        $product->setStatus($postItems[$entityId]['status']);
                    }
                    if(isset($postItems[$entityId]['visibility'])){
                        $product->setVisibility($postItems[$entityId]['visibility']);   
                    }
                    if(isset($postItems[$entityId]['qty'])){
                        if(isset($postItems[$entityId]['stock_status'])){
                            $product->setStockData(['qty' => $postItems[$entityId]['qty'], 'is_in_stock' => $postItems[$entityId]['stock_status']]); 
                            $product->setQuantityAndStockStatus(['qty' => $postItems[$entityId]['qty'], 'is_in_stock' => $postItems[$entityId]['stock_status']]);
                        }else{
                            $product->setStockData(['qty' => $postItems[$entityId]['qty'], 'is_in_stock' => 1]); 
                            $product->setQuantityAndStockStatus(['qty' => $postItems[$entityId]['qty'], 'is_in_stock' => 1]);  
                        }
                    }

                    try {
                        $product->save();
                        /*$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        $cacheManager = $objectManager->create('Magento\Framework\App\Cache\Manager');
                        $cacheManager->flush($cacheManager->getAvailableTypes());
                        $cacheManager->clean($cacheManager->getAvailableTypes());*/
                    } catch (\Exception $e) {
                        $messages[] = "[Error:]  {$e->getMessage()}";
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
}