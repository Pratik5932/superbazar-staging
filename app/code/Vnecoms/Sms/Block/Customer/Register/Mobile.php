<?php
namespace Vnecoms\Sms\Block\Customer\Register;

use Magento\Framework\View\Element\Template\Context;
use Vnecoms\Sms\Helper\Data as SmsHelper;
use GuzzleHttp\json_encode;
use Magento\Customer\Model\Session;

class Mobile extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Vnecoms\Sms\Helper\Data
     */
    protected $smsHelper;
    
    /**
     * @var Session
     */
    protected $customerSession;
    
    /**
     * @param Context $context
     * @param SmsHelper $smsHelper
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        SmsHelper $smsHelper,
        Session $customerSession,
        array $data = []
    ) {
        $this->smsHelper = $smsHelper;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer(){
        return $this->customerSession->getCustomer();
    }
    
    /**
     * Get otp length
     * 
     * @return int
     */
    public function getOtpLength(){
        return $this->smsHelper->getOtpLength();
    }
    
    /**
     * @return boolean
     */
    public function isEnabledVerifying(){
        return $this->smsHelper->isEnableVerifyingCustomerMobile() &&
            $this->smsHelper->isEnableVerifyingOnRegister();
    }
    
    /**
     * Send OTP URL
     * 
     * @return string
     */
    public function getSendOtpUrl(){
        return $this->getUrl('vsms/otp/send');
    }
    
    /**
     * Send OTP URL
     *
     * @return string
     */
    public function getVerifyOtpUrl(){
        return $this->getUrl('vsms/otp/verify');
    }
    
    /**
     * Get otp resend period time
     * 
     * @return number
     */
    public function getOtpResendPeriodTime(){
        return $this->smsHelper->getOtpResendPeriodTime();
    }
    
    /**
     * Initial Country
     * @return Ambigous <string, \Magento\Framework\App\Config\mixed>
     */
    public function getInitialCountry(){
        return $this->smsHelper->getInitialCountry();
    }
    
    /**
     * Get only countries
     * 
     * @return string
     */
    public function getOnlyCountries(){
        return $this->smsHelper->isAllowedAllCountries()?'[]':json_encode(explode(",",$this->smsHelper->getAllowedCountries()));
    }
    
    /**
     * Get preferred countries
     *
     * @return string
     */
    public function getPreferredCountries(){
        $preferredCountries = $this->smsHelper->getPreferredCountries();
        $preferredCountries = $preferredCountries?explode(',', $preferredCountries):["us", "vn"];
        return json_encode($preferredCountries);
    }
}
