<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Wallet
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Wallet\Plugin;

use Magento\Framework\App\RequestInterface;

class Creditmemo
{
    
    public $_objectManager;
    
     public $_request;
    
    public function __construct(\Magento\Framework\ObjectManagerInterface $ob,
                                RequestInterface $request
    )
	{
	   $this->_objectManager = $ob;
	   $this->_request = $request;
	}
		
    public function afterCanCreditmemo( \Magento\Sales\Model\Order $subject, $result)
    {
        $requestPath = $this->_request->getFullActionName();
        if($requestPath == "sales_order_view"){
            $flag = false;
        	if(count($subject->getAllItems()) == 1){
        		foreach ( $subject->getAllItems () as $item ) {
        			if ($item->getSku () == "wallet_product") {
        			   $flag= true;
        			}
        		}
        	}
        	if($flag){
        		return false;
        	}else{
        		return $result;
        	}
        }
    	
    		return $result;
    	
      
    }
}