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
namespace Ced\Wallet\Controller\Adminhtml\Wallet;

use \Magento\Backend\App\Action\Context;

use Magento\Framework\Message\Error;

class Validate extends \Magento\Backend\App\Action
{
    protected $resultJsonFactory;
    protected $_messageManager;
    protected $_employeeFactory;

    public function __construct(

        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Context $context

    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);

    }

     public function execute()
      { 
        $response = new \Magento\Framework\DataObject();
        $postData = $this->getRequest()->getPostValue();
        if($postData['action'] == 0){
          $minAmount = $this->_objectManager->get('Ced\Wallet\Helper\Data')->validateMinAmount($postData['amount']);
            if($minAmount['error']){
              $message = "Min Amount to add to the wallet is ". $minAmount['min_amount'] ;
              $response->setError(true);
              $response->setMessage($message);
            }  
        }
        
        
        return $this->resultJsonFactory->create()->setData($response);
      }


    }
