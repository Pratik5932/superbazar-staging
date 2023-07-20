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

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Framework\Controller\ResultFactory;

class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_creditmemo';

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $creditmemoLoader;

    /**
     * @var CreditmemoSender
     */
    protected $creditmemoSender;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader
     * @param CreditmemoSender $creditmemoSender
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader,
        CreditmemoSender $creditmemoSender,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->creditmemoLoader = $creditmemoLoader;
        $this->creditmemoSender = $creditmemoSender;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    
	public function execute()
	{
        $itemTotal = 0;
		$data=$this->getRequest()->getParams();

		$getorderId = (explode("/",$data['order_id']));
		$orderid = $getorderId[0];
		$order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderid);
		$allItems = $order->getAllItems();
		$incrementId = $order->getIncrementId();
		$ordertotal = $order->getTotalPaid();
		$customerId = $order->getCustomerId();
		$customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
		$baseToOrderRate = $order->getBaseToOrderRate();
		try{
			if($customer->getEnableWalletSystem() != 1){
				throw new \Magento\Framework\Validator\Exception(__('Wallet is disabled for user.'));
			}
		
			$transactionModel = $this->_objectManager->create('Ced\Wallet\Model\Transaction')->getCollection()->addFieldToFilter('order_id',$incrementId);
			$paidamount = 0;
			
			
			$credit = $this->createCreditMemo();
			if($credit == "error"){
			    return $this->_redirect('sales/order/view', array('order_id' => $orderid));
			}
			
			$walletAmount = $customer->getAmountWallet();
			$amount = $credit;
			
            $transactionCollection = $this->_objectManager->create('Ced\Wallet\Model\Transaction')->getCollection();
            $transactionCollection->addFieldToFilter('order_id', $order->getIncrementId());
            $transactionCollection->addFieldToFilter('is_cashback', 1);
            $transactionCollection->addFieldToFilter('customer_id', $order->getCustomerId());
            
            $cashbackAmount = 0;
            if(!empty($transactionCollection)){
                foreach($transactionCollection as $tc){
                    $cashbackAmount += $tc->getAmount();
                }
            }
            
			$time =$this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
			$currentTimestamp = $time->timestamp(time());
			$date = date('Y-m-d H:i:s', $currentTimestamp);
			$transaction = $this->_objectManager->create('Ced\Wallet\Model\Transaction');
				
			$transaction->setData('order_id',$incrementId);
			$transaction->setData('action','0');
			$transaction->setData('customer_id',$customerId);
			$transaction->setData('amount',$amount);
			$transaction->setData('created_at',$date);
			$transaction->save();
				
		    if($cashbackAmount){
		        $transaction = $this->_objectManager->create('Ced\Wallet\Model\Transaction');
    			$transaction->setData('order_id',$incrementId);
    			$transaction->setData('action','1');
    			$transaction->setData('customer_id',$customerId);
    			$transaction->setData('amount',$cashbackAmount);
    			$transaction->setData('created_at',$date);
    			$transaction->setData('comment', __('Cashback reverted for Order# %1', $order->getIncrementId()));
    			$transaction->save();
		    }
				
			$total = $credit + $walletAmount - $cashbackAmount;
			$customerData = $customer->getDataModel();
			$customerData->setCustomAttribute('amount_wallet', $total);
			$customer->updateData($customerData);
			$customer->save();	
				
			if(!$order->canCreditmemo()){
				$order->setData('state', "closed");
			    $order->setStatus("closed");
			    $order->save();
			}	
			
			$this->messageManager->addSuccessMessage('You saved Credit Memo & Amount Transferred To Wallet Successfully');
            return $this->_redirect('sales/order/view', array('order_id' => $orderid));
		} catch (\Exception $e){
			$this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('sales/order/view', array('order_id' => $orderid));
		 
		}
	}

	 public function createCreditMemo()
    {
    	$commentText ="";
        $data = $this->getRequest()->getPost('creditmemo');

        if (!empty($data['comment_text'])) {
        	$commentText = $data['comment_text'];
            $this->_getSession()->setCommentText($data['comment_text']);
        }
        try {
            
            $this->creditmemoLoader->setCreditmemo($this->getRequest()->getParam('creditmemo'));
            $this->creditmemoLoader->setInvoiceId($this->getRequest()->getParam('invoice_id'));
            $this->creditmemoLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $this->creditmemoLoader->setCreditmemoId($this->getRequest()->getParam('creditmemo_id'));
            $creditmemo = $this->creditmemoLoader->load();
            if ($creditmemo) {
                if (!$creditmemo->isValidGrandTotal()) {
                    $this->messageManager->addErrorMessage(__('The credit memo\'s total must be positive.'));
                    return 'error';
                }
                if (!empty($data['comment_text'])) {
                    $creditmemo->addComment(
                        $commentText,
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );

                    $creditmemo->setCustomerNote($commentText);
                    $creditmemo->setCustomerNoteNotify(isset($data['comment_customer_notify']));
                }

                if (isset($data['do_offline'])) {
                    if (!$data['do_offline'] && !empty($data['refund_customerbalance_return_enable'])) {
                        $this->messageManager->addErrorMessage(__('Cannot create online refund for Refund to Store Credit.'));
                        return 'error';
                    }
                }
                $creditmemoManagement = $this->_objectManager->create(
                    'Magento\Sales\Api\CreditmemoManagementInterface'
                );

                $creditmemoManagement->refund($creditmemo, (bool)$data['do_offline'], !empty($data['send_email']));

                if (!empty($data['send_email'])) {
                    $this->creditmemoSender->send($creditmemo);
                }
                return $creditmemo->getBaseGrandTotal();
            } else {
                return 'error';
            }
        }catch (\Exception $e) {
        	$this->messageManager->addErrorMessage($e->getMessage());
        	return 'error';
        }
    }
}