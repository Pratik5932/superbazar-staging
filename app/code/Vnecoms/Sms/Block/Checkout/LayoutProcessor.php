<?php

namespace Vnecoms\Sms\Block\Checkout;

/**
 * Class LayoutProcessor
 */
class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    /**
     * @var \Vnecoms\Sms\Helper\Data
     */
    protected $helper;
    
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    
    /**
     * @param \Vnecoms\Sms\Helper\Data $helper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Vnecoms\Sms\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->helper = $helper;
        $this->urlBuilder = $urlBuilder;
    }
    
    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        /*Shipping mobile*/
        $telephoneData = $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['config'];
        $telephoneData['initialCountry'] = strtolower($this->helper->getInitialCountry());
        $allowedCountries = $this->helper->isAllowedAllCountries();
        $telephoneData['onlyCountries'] = $allowedCountries?[]:$allowedCountries;
        $preferredCountries = $this->helper->getPreferredCountries();
        $preferredCountries = $preferredCountries?explode(',', $preferredCountries):["us", "vn"];
        $telephoneData['preferredCountries'] = $preferredCountries;
        $telephoneData['requireVerifying'] = $this->helper->isEnableVerifyingAddressMobile();
        $telephoneData['sendOtpUrl'] = $this->urlBuilder->getUrl('vsms/otp_checkout/send');
        $telephoneData['verifyOtpUrl'] = $this->urlBuilder->getUrl('vsms/otp_checkout/verify');
        $telephoneData['otpResendPeriodTime'] = $this->helper->getOtpResendPeriodTime();
        $telephoneData['defaultResendBtnLabel'] = __('Resend');
        
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['config'] = $telephoneData;
        return $jsLayout;
    }
}
