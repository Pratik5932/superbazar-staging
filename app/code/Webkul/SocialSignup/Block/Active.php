<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Block;

use Webkul\SocialSignup\Api\FacebooksignupRepositoryInterface;

class Active extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    
    /**
     * @var  \Magento\Framework\UrlInterface
     */
    protected $_urlinterface;
    
    /**
     * @var  \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @var storeManager
     */
    protected $_storeManager;
    
    /**
     * @var Webkul\MpSellerGroup\Api\FacebooksignupRepositoryInterface;
     */
    protected $_facebooksignupRepository;
    
    /**
     * @var Webkul\SocialSignup\Helper\Data;
     */
    private $helper;
    
    /**
     * @param Context $context
     * @param array $data
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        FacebooksignupRepositoryInterface $facebooksignupRepository,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Locale\Resolver $resolverObj,
        \Magento\Customer\Model\Session $session,
        \Webkul\SocialSignup\Helper\Data $helper,
        array $data = []
    ) {
    
        $this->_storeManager = $context->getEventManager();
        $this->_facebooksignupRepository = $facebooksignupRepository;
        $this->_customerSession = $session;
        $this->resolverObj = $resolverObj;
        $this->helper = $helper;
        $this->_urlinterface = $context->getUrlBuilder();
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_objectManager = $objectManager;
        parent::__construct($context, $data);
    }
    /**
     * get facebook app id
     * @return integer
     */
    public function getAppId()
    {
        return $this->helper->getFbAppId();
    }
    /**
     * get secret key of facebook
     * @return string
     */
    public function getSecretKey()
    {
        return $this->helper->getFbSecretKey();
    }
    /**
     * get current url of webpage
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlinterface->getCurrentUrl();
    }
    /**
     * check customer status
     * @return boolean
     */
    public function customerSession()
    {
        return $this->_customerSession->isLoggedIn();
    }
    /**
     * get locale code
     * @return string
     */
    public function getLocaleCode()
    {
        $resolver= $this->resolverObj;
        return $resolver->getLocale();
    }
    /**
     * check status of facebook user
     * @return integer
     */
    public function checkFbUser()
    {
        $uid=0;
        $customerId=$this->_customerSession->getCustomerId();
        $collection=$this->_facebooksignupRepository->getByCustomerId($customerId);
        foreach ($collection as $data) {
            if ($data['fb_id']) {
                $uid = $data['fb_id'];
            }
        }
        return $uid;
    }

    /**
     * get request url
     * @param  string $url   request url
     * @param  array $param contain params
     * @return string
     */
    public function getRequestUrl($url, $param)
    {
        return $this->_storeManager->getStore()->getUrl($url, $param);
    }
}
