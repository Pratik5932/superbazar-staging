<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Helper;

use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Result\PageFactory;
use Webkul\SocialSignup\Api\FacebooksignupRepositoryInterface;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Social Signup data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CHARS_LOWERS                          = 'abcdefghijklmnopqrstuvwxyz';
    const CHARS_UPPERS                          = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const CHARS_DIGITS                          = '0123456789';
    const CHARS_SPECIALS                        = '!$*+-.=?@^_|~';
    const CHARS_PASSWORD_LOWERS                 = 'abcdefghjkmnpqrstuvwxyz';
    const CHARS_PASSWORD_UPPERS                 = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    const CHARS_PASSWORD_DIGITS                 = '23456789';
    const CHARS_PASSWORD_SPECIALS               = '!$*-.=?@_';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $_storeManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $locale;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * __construct function
     *
     * @param Imagelink $imageLink
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $collectionFactory
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Webkul\SocialSignup\Model\FacebooksignupFactory $facebooksignupFactory
     * @param FacebooksignupRepositoryInterface $facebooksignupRepository
     * @param \Magento\Framework\Locale\Resolver $locale
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param HttpContext $httpContext
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Imagelink $imageLink,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $collectionFactory,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        PageFactory $resultPageFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Cms\Helper\Page $cmsPage,
        \Webkul\SocialSignup\Model\FacebooksignupFactory $facebooksignupFactory,
        FacebooksignupRepositoryInterface $facebooksignupRepository,
        \Magento\Framework\Locale\Resolver $locale,
        \Magento\Framework\Module\Manager $moduleManager,
        HttpContext $httpContext,
        EncryptorInterface $encryptor
    ) {
        $this->facebooksignupFactory = $facebooksignupFactory;
        $this ->_imageLink = $imageLink;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->collectionFactory = $collectionFactory;
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
        $this->cmsPage = $cmsPage;
        $this->_storeManager = $storeManager;
        $this->_resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        $this->coreSession = $coreSession;
        $this->_attributeCollection = $attributeCollection;
        $this->eavEntity = $eavEntity;
        $this->_facebooksignupRepository = $facebooksignupRepository;
        $this->url = $context->getUrlBuilder();
        $this->locale = $locale;
        $this->moduleManager = $moduleManager;
        $this->httpContext = $httpContext;
        $this->encryptor = $encryptor;
    }

    /**
     * has required attribute
     *
     * @return bool
     */
    public function getCustomerAttributes()
    {
        $filter = ['store_id','website_id','group_id','firstname','lastname','email'];
        $typeId = $this->eavEntity->setType('customer')->getTypeId();
        $collection = $this->_attributeCollection->create()
                    ->setEntityTypeFilter($typeId)
                    ->addFieldToFilter('attribute_code', ['nin'=>$filter])
                    ->addFieldToFilter('is_required', 1)
                    ->setOrder('sort_order', 'ASC');
        if ($collection->getSize()) {
            return true;
        }
        return false;
    }

    /**
     * get logger instance
     *
     * @return object
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * get facebook app id
     * @return string
     */
    public function getFbAppId()
    {
        return $this->encryptor->decrypt($this->_scopeConfig->getValue(
            'socialsignup/fblogin/appid',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * get facebook app secret key
     * @return string
     */
    public function getFbSecretKey()
    {
        return $this->encryptor->decrypt($this->_scopeConfig->getValue(
            'socialsignup/fblogin/secret',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * get Google client id
     * @return string
     */
    public function getGoogleClientId()
    {
        return $this->encryptor->decrypt($this->_scopeConfig->getValue(
            'socialsignup/google/api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * get Google secret key
     * @return string
     */
    public function getGoogleSecretKey()
    {
        return $this->encryptor->decrypt($this->_scopeConfig->getValue(
            'socialsignup/google/secret',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * get LinkedIn app id
     * @return string
     */
    public function getLinkedinAppId()
    {
        return $this->encryptor->decrypt($this->_scopeConfig->getValue(
            'socialsignup/linkedin/api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * get linkedin secret key
     * @return int
     */
    public function getLinkedinSecret()
    {
        return $this->encryptor->decrypt($this->_scopeConfig->getValue(
            'socialsignup/linkedin/secret',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * get cosumer key of twitter
     * @return integer
     */
    public function getConsumerKey()
    {
        return $this->encryptor->decrypt($this->_scopeConfig->getValue(
            'socialsignup/twitterlogin/consumerkey',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * get secrete key of twitter
     * @return string
     */
    public function getConsumerSecret()
    {
        return $this->encryptor->decrypt($this->_scopeConfig->getValue(
            'socialsignup/twitterlogin/consumersecret',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * get application key of instagram
     * @return integer
     */
    public function getInstaClientId()
    {
        return $this->encryptor->decrypt($this->_scopeConfig->getValue(
            'socialsignup/instagram/api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * get secrete key of instagram
     * @return string
     */
    public function getInstaSecretKey()
    {
        return $this->encryptor->decrypt($this->_scopeConfig->getValue(
            'socialsignup/instagram/secret',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * check the status of facebook
     * @return boolean
     */
    public function getFbStatus()
    {
        return $this->_scopeConfig->getValue(
            'socialsignup/fblogin/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * check the status of google
     * @return boolean
     */
    public function getGoogleStatus()
    {
        return $this->_scopeConfig->getValue(
            'socialsignup/google/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * check the status of linkedIn
     * @return boolean
     */
    public function getLinkedInStatus()
    {
        return $this->_scopeConfig->getValue(
            'socialsignup/linkedin/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * check the status of twitter
     * @return boolean
     */
    public function getTwitterStatus()
    {
        return $this->_scopeConfig->getValue(
            'socialsignup/twitterlogin/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * check the status of instgram
     * @return boolean
     */
    public function getInstaStatus()
    {
        return $this->_scopeConfig->getValue(
            'socialsignup/instagram/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * get Customer session
     * @return boolean
     */
    public function customerSession()
    {
        return $this->_customerSession->isLoggedIn();
    }

    /**
     * get facbook image
     * @return string
     */
    public function getLoginImg()
    {
        $img = $this->_scopeConfig->getValue(
            'socialsignup/fblogin/imglogin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (empty($img)) {
             $img = $this ->_imageLink->getWebImageLink('Webkul_SocialSignup::images/icon-facebook.png');
        } else {
            $img = $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ).'socialsignup/fb/' . $img;
        }
        return $img;
    }

    /**
     * get twitter image
     * @return string
     */
    public function getTwitterLoginImg()
    {
        $img = $this->_scopeConfig->getValue(
            'socialsignup/twitterlogin/imglogin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (empty($img)) {
            $img = $this ->_imageLink->getWebImageLink('Webkul_SocialSignup::images/icon-twitter.png');
        } else {
            $img = $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ).'socialsignup/twitter/' . $img;
        }
        return $img;
    }

    /**
     * get google login image
     * @return string
     */
    public function getGoogleLoginImg()
    {
        $img = $this->_scopeConfig->getValue(
            'socialsignup/google/imglogin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (empty($img)) {
            $img = $this ->_imageLink->getWebImageLink('Webkul_SocialSignup::images/icon-google.png');
        } else {
            $img = $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ).'socialsignup/google/' . $img;
        }
        return $img;
    }

    /**
     * get LinkedIn login image
     * @return string
     */
    public function getLinkedinLoginImg()
    {
        $img = $this->_scopeConfig->getValue(
            'socialsignup/linkedin/imglogin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (empty($img)) {
            $img = $this ->_imageLink->getWebImageLink('Webkul_SocialSignup::images/icon-linkedin.png');
        } else {
            $img = $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ).'socialsignup/linkedin/' . $img;
        }
        return $img;
    }

    /**
     * get instagram login image
     * @return string
     */
    public function getInstaLoginImg()
    {
        $img = $this->_scopeConfig->getValue(
            'socialsignup/instagram/imglogin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (empty($img)) {
            $img = $this ->_imageLink->getWebImageLink('Webkul_SocialSignup::images/icon-Instagram.png');
        } else {
            $img = $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ).'socialsignup/instagram/' . $img;
        }
        return $img;
    }

    /**
     * redirect to 404 cms page
     * @param  Action $frontController object of controller
     * @return redirect to url
     */
    public function redirect404($frontController)
    {
        $pageHelper = $this->cmsPage;
        $pageId = $this->_scopeConfig->getValue('web/default/cms_no_route');
        $resultPage = $pageHelper->prepareResultPage($frontController, $pageId);
        if ($resultPage) {
            $resultPage->setStatusHeader(404, '1.1', 'Not Found');
            $resultPage->setHeader('Status', '404 File not found');
            return $resultPage;
        }
    }

    /**
     * getnerate password
     * @param  integer $length length of password
     * @return string
     */
    public function generatePassword($length = 8)
    {
        $chars = self::CHARS_PASSWORD_LOWERS
            . self::CHARS_PASSWORD_UPPERS
            . self::CHARS_PASSWORD_DIGITS
            . self::CHARS_PASSWORD_SPECIALS;
         return   $this->getRandomString($length, $chars);
    }

    /**
     * generate password with ramdom string
     * @param  integer $len   length of password
     * @param  string $chars string of charcters
     * @return string
     */
    protected function getRandomString($len, $chars = null)
    {
        if ($chars==null) {
            $chars = self::CHARS_LOWERS . self::CHARS_UPPERS . self::CHARS_DIGITS;
        }
        for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++) {
            $str .= $chars[random_int(0, $lc)];
        }
        return $str;
    }

    /**
     * refresh the page
     */
    public function _loginFinalize($objectAction)
    {
        $resultPage = $this->_resultPageFactory->create();
        $block = $resultPage->getLayout()
                ->createBlock(\Magento\Framework\View\Element\Template::class)
                ->setTemplate('Webkul_SocialSignup::socialsignup/window-close.phtml')
                ->toHtml();
        $objectAction->getResponse()->setBody($block);
    }

    /**
     * closed popup
     */
    public function closeWindow($objectAction)
    {
        $resultPage = $this->_resultPageFactory->create();
        $block = $resultPage->getLayout()
                ->createBlock(\Magento\Framework\View\Element\Template::class)
                ->setTemplate('Webkul_SocialSignup::socialsignup/window-close.phtml')
                ->toHtml();
        $objectAction->getResponse()->setBody($block);
    }

    public function getCoreSession()
    {
        return $this->coreSession;
    }

    /**
     * set api resonse in session
     *
     * @param array $customerData
     * @return void
     */
    public function setInSession($customerData)
    {
        $this->getCoreSession()->setData('social_signup_data', $customerData);
    }

    /**
     * get api response from session
     *
     * @return array
     */
    public function getFromSession()
    {
        return $this->getCoreSession()->getData('social_signup_data');
    }

    /**
     * get facebook table instance
     *
     * @return object
     */
    public function getFacebookTbInstace()
    {
        return $this->facebooksignupFactory->create();
    }

    /**
     * clear session
     *
     * @return void
     */
    public function clearSession()
    {
        $this->getCoreSession()->setData('social_signup_data', '');
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
     * getLocaleCode function
     *
     * @return string
     */
    public function getLocaleCode()
    {
        return $this->locale->getLocale();
    }

    /**
     * getRequestUrl function
     *
     * @param string $url
     * @param array $param
     * @return string
     */
    public function getRequestUrl($url = '', $param = [])
    {
        return $this->_storeManager->getStore()->getUrl($url, $param);
    }

    /**
     * getUrl function
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->url->getUrl($route, $params);
    }

    /**
     * getStatus function
     *
     * @return bool
     */
    public function getStatus()
    {
        if ($this->getFbStatus() == 1 || $this->getTwitterStatus() == 1 ||
            $this->getGoogleStatus() == 1 || $this->getLinkedInStatus() == 1 ||
            $this->getInstaStatus() == 1) {
                return 1;
        }
        return 0;
    }

    /**
     * getModuleStatus function
     *
     * @return bool
     */
    public function getModuleStatus()
    {
        return $this->_scopeConfig->getValue(
            'socialsignup/sociallogin/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isModuleEnabled($moduleName = '')
    {
        return $this->moduleManager->isOutputEnabled($moduleName);
    }

    /**
     * getSocialSignupConfiguration function
     *
     * @return array
     */
    public function getSocialSignupConfiguration()
    {
        return [
                'fb_status'=>(int)$this->getFbStatus(),
                'google_status'=>(int)$this->getGoogleStatus(),
                'twitter_status'=>(int)$this->getTwitterStatus(),
                'linkedin_status'=>(int)$this->getLinkedInStatus(),
                'insta_status'=>(int)$this->getInstaStatus(),
                'fbAppId'=>$this->getFbAppId(),
                'uId'=>$this->checkFbUser(),
                'localeCode'=>$this->getLocaleCode(),
                'fbLoginUrl'=>$this->getUrl('socialsignup/facebook/login'),
                'status'=>$this->socialLoginBtnStatus(),
                'loginImg'=> $this->getLoginImg(),
                'twitterLoginImg' => $this->getTwitterLoginImg(),
                'googleLoginImg' => $this->getGoogleLoginImg(),
                'LinkedinLoginImg' => $this->getLinkedinLoginImg(),
                'InstaLoginImg' => $this->getInstaLoginImg(),
                'socialSignupModuleEnable' => (int)$this->getModuleStatus(),
                'popupData'=>[
                    "width"=>'700',
                    "height" => '300',
                    "twitterUrl" => $this->getRequestUrl('socialsignup/twitter/request', ['mainw_protocol'=>'http']),
                    "linkedinUrl" => $this->getRequestUrl(
                        'socialsignup/linkedin/request',
                        ['mainw_protocol'=>'http']
                    ),
                    "googleUrl" => $this->getRequestUrl('socialsignup/google/request', ['mainw_protocol'=>'http']),
                    "instagramUrl" => $this->getRequestUrl(
                        'socialsignup/instagram/request',
                        ['mainw_protocol'=>'http']
                    )
                ],
                'isCustomerLoggedIn' => $this->isCustomerLoggedIn(),
                'getMessagesUrl' => $this->getUrl('socialsignup/message/check'),
            ];
    }

    /**
     * socialLoginBtnStatus function to check that any of single social login option enable or not.
     *
     * @return boolean
     */
    public function socialLoginBtnStatus()
    {
        if (($this->getFbStatus() == 1 || $this->getTwitterStatus() == 1 ||
            $this->getGoogleStatus() == 1 || $this->getLinkedInStatus() == 1 ||
            $this->getInstaStatus() == 1) && $this->isCustomerLoggedIn() == 0) {
            return 1;
        }
         return 0;
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isCustomerLoggedIn()
    {
        return (bool)$this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }
}
