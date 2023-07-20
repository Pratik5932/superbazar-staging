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

class Mtsave extends \Magento\Backend\App\Action
{
    const WALLET_TRANSACTION_EMAIL_TEMPLATE = 'ced_wallet/active/mail_template_for_transaction';

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
        \Ced\Wallet\Helper\Email $email,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->email = $email;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    
	public function execute()
	{
        $data = $this->getRequest()->getParams();
        $customerId = isset($data['customer_id'])?$data['customer_id']:0;
        if($customerId){
            $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
            $email = $customer->getEmail();
            $name = $customer->getFirstname();
        }
       
        try{
            if($customer->getEnableWalletSystem() != 1){
                //wallet system is disabled for customer
                throw new \Magento\Framework\Validator\Exception(__('Wallet is disabled for user.'));
            }
            $amount = $data['amount'];
            $walletAmount = $customer->getAmountWallet();
            if($data['action'] == \Ced\Wallet\Model\Transaction::CREDIT){
                $total =  $walletAmount + $amount;
                $msg = "Amount Transferred to Wallet Successfully" ;
                $orderInfo = "Admin Credit";
            }else{
                if($walletAmount >= $amount ){
                    $total = $walletAmount - $amount;
                    $msg = "Amount Transferred from Wallet Successfully" ;
                    $orderInfo = "Admin Debit";
                }else{
                     $this->messageManager->addErrorMessage(__("Customer has not enough amount for deduction"));
                     return $this->_redirect('wallet/wallet/transactions');
                }
             }
            
            
           
            $customerData = $customer->getDataModel();
            $customerData->setCustomAttribute('amount_wallet', $total);
            $customer->updateData($customerData);
            $customer->save();
            $time =$this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
            
            $currentTimestamp = $time->timestamp(time());
            $date = date('Y-m-d H:i:s', $currentTimestamp);
            $transaction = $this->_objectManager->create('Ced\Wallet\Model\Transaction');
                
            $transaction->setData('order_id',$orderInfo);
            $transaction->setData('action',$data['action']); //0 for credit
            $transaction->setData('customer_id',$customerId);
            $transaction->setData('amount',$amount);
            $transaction->setData('comment',$data['comment']);
            $transaction->setData('created_at',$date);
            $transaction->save();
            if($data['action']==0)
            $trasactionMode="Credited";
            else
            $trasactionMode="Debited";
            
            $emailData = [
                            'transaction_mode' => $trasactionMode,
                            'amount' =>  $this->_objectManager->get('Ced\Wallet\Helper\Data')->formatPrice($amount)
                        ];
            $template = self::WALLET_TRANSACTION_EMAIL_TEMPLATE;
            $this->email->sendEmail($email,$name,$emailData,$template);
            $this->messageManager->addSuccessMessage(__( $msg ));
            return $this->_redirect('wallet/wallet/transactions');
                
        } catch (\Exception $e){
            $this->messageManager->addErrorMessage(__($e->getMessage()));
           return $this->_redirect('wallet/wallet/transactions');

        }
	}
}