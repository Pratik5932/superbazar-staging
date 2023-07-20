<?php

namespace Superbazaar\General\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_storeManager;
    protected $_formKey;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context, 
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Url\Helper\Data $url
    ) {
        $this->_storeManager = $storeManager;
        $this->formKey = $formKey;
        $this->url = $url;
        parent::__construct($context);
    }

    public function getBaseUrl() {
        return $this->_storeManager->getStore()->getBaseUrl();
    }


    public function getMediaUrl() {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getConfigValue($value = '') {
        return $this->scopeConfig->getValue($value, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
	public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
	

}
