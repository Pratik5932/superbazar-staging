<?php

namespace Superbazaar\CustomWork\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\DB\Select;

class ZipConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    public function __construct(
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->_storeManager = $storeManager;
    }

    public function getConfig()
    {
        $output['configdata'] = ['zipcode' => $this->getCurrentZipcode(), 'baseurl' => $this->getBaseurl()];
        return $output;
    }

    public function getCurrentZipcode(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $address = $objectManager->get('Webkul\MpHyperLocal\Helper\Data')->getSavedAddress();
        $shipzipcode = $address ? $address['address'] :'';
        return $shipzipcode;
    }

    public function getBaseurl(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()->getBaseUrl();
        return $baseUrl;
    }
}