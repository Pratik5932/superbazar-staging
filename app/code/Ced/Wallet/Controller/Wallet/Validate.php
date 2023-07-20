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
namespace Ced\Wallet\Controller\Wallet;

class Validate extends \Magento\Framework\App\Action\Action
{
    protected $_customerSession;
   
    protected $_scopeConfig;
    protected $resultPageFactory;
    protected $resultJsonFactory;
    
    public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
    \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
       
    } 
    /**
     * Load the page defined in view/frontend/layout/samplenewpage_index_index.xml
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
    	$data = $this->getRequest()->getPostValue();
    	$minAmount = $this->_objectManager->get('Ced\Wallet\Helper\Data')->validateMinAmount($data['amount']);
    	if($minAmount['error']){
    		$message = "Min Amount to add to the wallet is ". $minAmount['min_amount'] ;
    		$response = ['error'=>true,'msg' =>$message] ;
    	}else{
    		$response = ['error'=>false,'msg' =>''] ;
    	}
    	$resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }
}