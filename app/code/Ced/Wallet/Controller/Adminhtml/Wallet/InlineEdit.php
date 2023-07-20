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

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;


class InlineEdit extends \Magento\Backend\App\Action
{
    /** @var BlockRepository  */
    protected $requestRepository;

    /** @var JsonFactory  */
    protected $jsonFactory;

    /**
     * @param Context $context
     * @param BlockRepository $blockRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        \Ced\Wallet\Model\Request $requestRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->requestRepository = $requestRepository;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
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
                foreach (array_keys($postItems) as $requestId) {
                    /** @var \Ced\Wallet\Model\Request $request */
                    $request = $this->_objectManager->create('Ced\Wallet\Model\Request')->load($requestId);

                    try {
                        
                        if(($postItems[$requestId]['status'] == \Ced\Wallet\Model\Request::PENDING) && $postItems[$requestId]['comment']!=''){
                            $error = true;
                            $messages[] = __('Comments are not allowed in pending status.');
                        }
                        if(($request->getStatus() == \Ced\Wallet\Model\Request::APPROVED)){
                            $error = true;
                            $messages[] = __('Changes are not allowed after Approved.');
                        }
                        
                        if($request->getStatus() != \Ced\Wallet\Model\Request::APPROVED && $postItems[$requestId]['status'] == \Ced\Wallet\Model\Request::APPROVED){ 
                            $request->setData(array_merge($request->getData(),$postItems[$requestId])); 
                            $orderId = "Redeem Request";
                            $customerId = $request->getData('customer_id');
                            $amount = $request->getData('amount');
                            $msg =  $request->getData('comment'); 
                            $action = \Ced\Wallet\Model\Transaction::DEBIT;
                            $walletUpdate =  $this->_objectManager->create('Ced\Wallet\Helper\Data')->updateCustomerWallet($orderId ,$customerId,$amount , $msg,$action );
                            if($walletUpdate['error']){
                                $messages[] = __($walletUpdate['msg']);
                                $error = true;
                            }
                            if(!$error){
                                $request->save($request);
                            }
                                
                        }elseif($request->getStatus() != \Ced\Wallet\Model\Request::APPROVED && $postItems[$requestId]['status'] == \Ced\Wallet\Model\Request::DISAPPROVED){
                            $request->setData(array_merge($request->getData(),$postItems[$requestId])); 
                            $request->save($request);
                        }
                        
                    } catch (\Exception $e) {
                        $messages[] = __('Something Went Wrong');
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
