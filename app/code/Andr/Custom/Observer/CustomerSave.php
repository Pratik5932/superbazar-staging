<?php

namespace Andr\Custom\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface;

class CustomerSave implements ObserverInterface
{
	
	 private $cookieManager;

     private $cookieMetadataFactory;
     
     private $jsonHelper;
	
	private $sessionManager;

    public function __construct(
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Superbazaar\CustomWork\Model\UserAgentFactory $useragent,
        SessionManagerInterface $sessionManager
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->jsonHelper = $jsonHelper;
        $this->useragent = $useragent;
        $this->sessionManager = $sessionManager;
    }

    public function setCustomCookie($cookie_name, $str)
    {
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $publicCookieMetadata->setDuration(86400);//OneYear
        //$publicCookieMetadata->setPath('/');
        $publicCookieMetadata->setHttpOnly(false);
        $publicCookieMetadata->setPath($this->sessionManager->getCookiePath());
        $publicCookieMetadata->setDomain($this->sessionManager->getCookieDomain());
        if($cookie_name == 'hyper_local'){
			$jsonAddressData = $this->jsonHelper->jsonEncode($str);
		} else {
			$jsonAddressData = $str;
		}        
		
        return $this->cookieManager->setPublicCookie(
            $cookie_name,
            $jsonAddressData,
            $publicCookieMetadata
        );
    }
    
    public function getCustomCookie()
    {
        return $this->cookieManager->getCookie(
            'magento2cookie'
        );
    }    
    /*
    public function __construct(
        Context $context,
        JsonHelper $jsonHelper,
        HttpContext $httpContext,
        JsonFactory $jsonFactory,
        SessionManagerInterface $sessionManager,
        CookieMetadataFactory $cookieMetadata,
        CookieManagerInterface $cookieManager,
        
        
		
		\Magento\Framework\HTTP\Header $httpHeader
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->httpContext = $httpContext;
        $this->jsonFactory = $jsonFactory;
        $this->sessionManager = $sessionManager;
        $this->cookieMetadata = $cookieMetadata;
        $this->cookieManager = $cookieManager;
        
        
		
		$this->httpHeader = $httpHeader;
        //parent::__construct($context);
    }
    */

    public function deleteCookie($str) {
        if ($this->cookieManager->getCookie($str)) {
            $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
            $metadata->setPath('/');
			$metadata->setDuration(86400);			//OneYear
			$metadata->setHttpOnly(false);
            $metadata->setPath($this->sessionManager->getCookiePath());
            $metadata->setDomain($this->sessionManager->getCookieDomain());
            
            return $this->cookieManager->deleteCookie($str,$metadata);
        }
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer) {
        
        $customer = $observer->getEvent()->getCustomer();
        $this->deleteCookie('hyper_local');
        $this->deleteCookie('zipcode');
        $this->setCustomCookie('hyper_local',Array("address"=>"3060","lat"=>"","lng"=>"","city"=>"","state"=>"","country"=>"","zipcode"=>$customer->getShippingCode()));
        
        //$this->setCustomCookie('zipcode',$customer->getShippingCode());
		$this->setCustomCookie('zipcode',trim($customer->getShippingCode(),'"'));

        $uniqueId = md5(($_SERVER['HTTP_USER_AGENT'] ?? "").($_SERVER['LOCAL_ADDR'] ?? "").($_SERVER['LOCAL_PORT'] ?? "").($_SERVER['REMOTE_ADDR'] ?? ""));
		
		$userAgentCollection = $this->useragent->create()->getCollection()->addFieldToFilter("useragent", ["eq" => $uniqueId]);
					
				if ($userAgentCollection->getSize()) {
					$userAgentCollection->setPageSize(1)->getFirstItem()->setZipcode($customer->getShippingCode())->save();
				} else {
					$this->useragent->create()->setUseragent($uniqueId)->setZipcode($customer->getShippingCode())->setCreatedAt(date('Y-m-d H:i:s'))->setUpdatedAt(date('Y-m-d H:i:s'))->save();
				}        
        
    }
}