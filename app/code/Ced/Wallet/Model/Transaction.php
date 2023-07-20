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
namespace Ced\Wallet\Model;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Transaction extends \Magento\Framework\Model\AbstractModel
{

    const CREDIT = 0;

    const DEBIT = 1;
    const EXPIRATION_PENDING = 0;
    const EXPIRATION_COMPLETED = 1;
    const INTEGER = 'integer';
    const STRING = 'string';
    
    const WALLET_TRANSACTION_EMAIL_TEMPLATE = 'ced_wallet/active/mail_template_for_transaction';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;
    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTime;

	public function _construct()
	{
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $this->messageManager = $objectManager->create('Magento\Framework\Message\ManagerInterface');
        $this->scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->timezone = $objectManager->create('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $this->_dateTime = $objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
        $this->_customerFactory = $objectManager->create('Magento\Customer\Model\CustomerFactory');
        $this->_dataHelper = $objectManager->create('Ced\Wallet\Helper\Data');
        $this->_mailHelper = $objectManager->create('Ced\Wallet\Helper\Email');
        $this->_init('Ced\Wallet\Model\ResourceModel\Transaction');
	}

	public function getTransactionActions(){
	    $actions = [
            self::CREDIT => __('Credit'),
            self::DEBIT => __('Debit'),
        ];
	    return $actions;
    }

    /**
     * Processing object before save data
     *
     * @return
     */
    public function beforvvvvveSave()
    {
        try{
            $amount = $this->getAmount();
            if ($amount){
                $minAmount = $this->scopeConfig->getValue(
                    'ced_wallet/active/min_amount',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                $maxAmount = $this->scopeConfig->getValue(
                    'ced_wallet/active/max_amount',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );

                /*check if current amount is minimum than the allowed amount*/
                if($amount < $minAmount){
                    $allowToSave = false;
                    throw new \Exception(__('Minimum transaction amount for wallet is %1', $minAmount));
                }

                /*check if current amount is greeater than the allowed amount*/
                if($amount > $maxAmount){
                    $allowToSave = false;
                    throw new \Exception(__('Maximum transaction amount for wallet is %1', $maxAmount));
                }

                /*check if current amount is greater than the maximum monthly allowed limit*/
                $usedAmount = $this->getCurrentMonthTransactionAmount();
                if($usedAmount  > $maxAmount){
                    $allowToSave = false;
                    throw new \Exception(__('You are Exhausted with your monthly usage limit %1', $maxAmount));
                }
                return parent::beforeSave();
            }
        }catch (\Exception $e){
            $this->_dataSaveAllowed = false;
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this;
        }
    }

    /**
     * get Current Month Transaction Amount from customer id and actoin
     *
     * @param customerId int | null
     * @param payment_action int | null
     * @return int
     */
    public function getCurrentMonthTransactionAmount($customerId = 0, $action = self::CREDIT){
        if ($customerId == 0){
            $customerId = $this->getCustomerId();
        }
        if (!isset($customerId) && $customerId ==0){
            return 0;
        }
        $current_date = $this->timezone->date()->format('y-m-d H:i:s');
        $start_date = $this->timezone->date()->format('y-m-01 H:i:s');

        $collection = $this->getCollection()
                            ->addFieldToFilter('customer_id', $customerId)
                            ->addFieldToFilter('action', $action)
                            ->addFieldToFilter('created_at', ['from' => $start_date])
                            ->addFieldToFilter('created_at', ['to' => $current_date]);
        
        $collection->getSelect()
            ->reset('columns')
            ->columns("SUM(amount) AS amount");

        return ($collection->getFirstItem()->getAmount())?$collection->getFirstItem()->getAmount():0;
    }

    /**
     * transfer money from one customer wallet to another
     *
     * @param from_customer string(email)
     * @param to_customer string(email)
     * @param to_amount int | object
     * @param order_id int | string
     * @param websiteId int | 1
     * @return bool
     */
    public function walletTransfer(
        $from,
        $to,
        $amount,
        $order_id = 'Wallet-Transfer',
        $description = ' ',
        $websiteId = 1){

        try{
            $fromCustomer = $this->_customerFactory->create();
            $fromCustomer->setWebsiteId($websiteId); 
            $fromCustomer->loadByEmail($from);
            
            if (!$fromCustomer->getId()){
                throw new \Exception('Unable To Find email, please check email-id and try again.');
            }

            $toCustomer = $this->_customerFactory->create();
            $toCustomer->setWebsiteId($websiteId); 
            $toCustomer->loadByEmail($to);
            
            if (!$toCustomer->getId()){
                throw new \Exception('Unable To Find email, please check email-id and try again.');
            }
            if ($fromCustomer->getEmail() ==  $toCustomer->getEmail()){
                throw new \Exception('Wallet transfer for same account is not allowed.');
            }
            $avaliableAmount = $fromCustomer->getAmountWallet();
            if($avaliableAmount == $amount){
                $leftamount = 0;
            }elseif($avaliableAmount > $amount){
                $leftamount = $avaliableAmount -$amount;
            }else{
                throw new \Exception('Entered amount is greater than the avaliable amount in wallet.');
            }
            /*save data in from customer */
            $wallet = $this;
            $payeewallet = clone $this;

            $wallet->setData('order_id', $order_id );
            $wallet->setData('amount', floatval($amount));
            $wallet->setData('customer_id', $fromCustomer->getId());
            $wallet->setData('action', self::DEBIT);
            $wallet->setData('comment', $description);
            $wallet->setData('created_at', $this->_dateTime->gmtDate());
            $wallet->setData('transaction_with', $toCustomer->getEmail());
            $wallet->save();
            $templateVars = $wallet->getData();
            $templateVars['transaction_mode'] = __('Debited');
            
            $this->_mailHelper->sendEmail($fromCustomer->getEmail(), $fromCustomer->getFirstname(),$templateVars, self::WALLET_TRANSACTION_EMAIL_TEMPLATE);
            /*to prevent further execution*/
            if (!$wallet->getId()){
                throw new \Exception('Unable To Transfer money.');
            }
            /*update customer attribute*/
            $customerData = $fromCustomer->getDataModel();
            $customerData->setCustomAttribute('amount_wallet', floatval($leftamount));
            $fromCustomer->updateData($customerData);
            $fromCustomer->getResource()->save($fromCustomer);

            /*
             * Start: update data for the payee customer
             **/
            $payeewallet->setData('order_id', $order_id );
            $payeewallet->setData('amount', floatval($amount));
            $payeewallet->setData('customer_id', $toCustomer->getId());
            $payeewallet->setData('action', self::CREDIT);
            $payeewallet->setData('comment', $description);
            $payeewallet->setData('created_at', $this->_dateTime->gmtDate());
            $payeewallet->setData('transaction_with', $fromCustomer->getEmail());
            $payeewallet->save();
            
            $templateVars = $payeewallet->getData();
            $templateVars['transaction_mode'] = __('Credited');
            
            
            $this->_mailHelper->sendEmail($toCustomer->getEmail(), $toCustomer->getFirstname(), $templateVars, self::WALLET_TRANSACTION_EMAIL_TEMPLATE);
            /*to prevent further execution*/
            if (!$payeewallet->getId()){
                throw new \Exception('Unable To Transfer money on email %1, please contact to the admin.');
            }
            /*4059880211539686 11 22 007*/
            /*update customer attribute*/
            $newAmount = $toCustomer->getAmountWallet() + $amount;/*current amoount + new received amount*/
            $customerData = $toCustomer->getDataModel();
            $customerData->setCustomAttribute('amount_wallet', floatval($newAmount));
            $toCustomer->updateData($customerData);
            $toCustomer->getResource()->save($toCustomer);

            $this->messageManager->addSuccessMessage(__('Successfully transferred %1 to %2 wallet.',[$this->_dataHelper->formatPrice($amount),$toCustomer->getName()]));
            return true;
        }catch(\Exception $e){
            $this->messageManager->addErrorMessage($e->getMessage());
            return false;
        }
    }
}