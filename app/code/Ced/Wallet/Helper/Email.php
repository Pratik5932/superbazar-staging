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
namespace Ced\Wallet\Helper;

use Magento\Framework\Math\Random;

class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    const WALLET_OTP_EMAIL_TEMPLATE = 'ced_wallet/active/mail_template_for_otp';

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     */
    private $_transportBuilder;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var Random
     */
    protected $_random;

    /**
     * @var GenerateOTP
     */
    protected $_otp;

    public function __construct(
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $_scopeConfig,
        Random $_random,
        GenerateOTP $_otp
    )
    {
        $this->inlineTranslation = $inlineTranslation;
        $this->_random = $_random;
        $this->_otp = $_otp;
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $_scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        parent::__construct($context);
    }

    /*
    * @note: send otp via email for wallet transaction
    *
    */
    public function sendOtp($email, $name, $emailData = [])
    {
        if (!isset($emailData['store'])){
            $store_id = 0;
        }else{
            $store_id = $emailData['store'];
        }
        try {
            $emailTemplateVariables = $emailData;
            $otp = $this->_otp->generateOTP($store_id);
            $emailTemplateVariables['transaction_otp'] = $otp;
 
            $adminMail = $this->_scopeConfig
                ->getValue('trans_email/ident_sales/email',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $adminDetail = [
                'name' => $name,
                'email' => $adminMail/*'davidwatson1090@gmail.com'*/,
            ];/*$adminMail*/

            $this->_transportBuilder
                ->setTemplateIdentifier($this->_scopeConfig->getValue(self::WALLET_OTP_EMAIL_TEMPLATE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                    ])
                ->setTemplateVars($emailTemplateVariables)
                ->setFrom($adminDetail)
                ->addTo($email, $name)
                ->getTransport()
                ->sendMessage();

            return ['result' => true, 'otp' => $emailTemplateVariables['transaction_otp']];
        } catch (\Exception $e) {
            return ['result' => false, 'otp' => null]; 
        }
    }
    
    public function sendEmail($email, $name, $template , $emailData = [])
    {

       $this->inlineTranslation->suspend();
        try {
            $adminMail = $this->_scopeConfig
                ->getValue('trans_email/ident_sales/email',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    
            $adminDetail = [
                'name' => $name,
                'email' => $adminMail,
            ];

            $this->_transportBuilder
                ->setTemplateIdentifier($this->_scopeConfig->getValue($template, \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DISTRO_STORE_ID
                    ])
                ->setTemplateVars($emailData)
                ->setFrom($adminDetail)
                ->addTo($email, $name)
                ->getTransport()
                ->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
           $this->inlineTranslation->resume();
        }
    }
}