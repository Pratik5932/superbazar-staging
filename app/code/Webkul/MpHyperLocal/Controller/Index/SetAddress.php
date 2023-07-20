<?php
/**
* Webkul Software.
*
* @category  Webkul
* @package   Webkul_MpHyperLocal
* @author    Webkul
* @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
* @license   https://store.webkul.com/license.html
*/
namespace Webkul\MpHyperLocal\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Webkul\MpHyperLocal\Helper\Data as HelperData;
use Magento\Quote\Api\CartRepositoryInterface;


class SetAddress extends Action
{
    /**
    * Name of cookie that holds private content version
    */
    const COOKIE_NAME = 'hyper_local';

    /**
    * @var Magento\Framework\Controller\Result\JsonFactory
    */
    private $jsonFactory;

    public $quoteRepository;


    /**
    * @var Magento\Framework\Session\SessionManagerInterface
    */
    private $sessionManager;

    /**
    * @var Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
    */
    private $cookieMetadata;

    /**
    * @var Magento\Framework\Stdlib\CookieManagerInterface
    */
    private $cookieManager;

    /**
    * @var HttpContext
    */
    private $httpContext;

    /**
    * @var JsonHelper
    */
    private $jsonHelper;

    private $useragent;
    private $httpHeader;
    protected  $_modelCart;
    protected  $_itemmodel;
    protected  $_productmodel;


    /**
    * @param Context $context
    * @param PageFactory $resultPageFactory
    */
    public function __construct(
        Context $context,
        JsonHelper $jsonHelper,
        HttpContext $httpContext,
        JsonFactory $jsonFactory,
        SessionManagerInterface $sessionManager,
        CookieMetadataFactory $cookieMetadata,
        CookieManagerInterface $cookieManager,
        CheckoutSession $checkoutSession,
        HelperData $helperData,
        \Superbazaar\CustomWork\Model\UserAgentFactory $useragent,
        \Magento\Framework\HTTP\Header $httpHeader,
		\Magento\Quote\Model\Quote\Item $itemmodel,
		\Magento\Catalog\Model\Product $productmodel,
		 CartRepositoryInterface $quoteRepository,
        Cart $modelCart
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->httpContext = $httpContext;
        $this->jsonFactory = $jsonFactory;
        $this->sessionManager = $sessionManager;
        $this->cookieMetadata = $cookieMetadata;
        $this->cookieManager = $cookieManager;
        $this->checkoutSession = $checkoutSession;
        $this->helperData = $helperData;
        $this->useragent = $useragent;
        $this->httpHeader = $httpHeader;
        $this->_modelCart = $modelCart;
        $this->_itemmodel = $itemmodel;
        $this->_productmodel = $productmodel;
		 $this->quoteRepository = $quoteRepository;
        parent::__construct($context);
    }

    /**
    * Set Address
    *
    * @return \Magento\Framework\View\Result\Page
    */
    public function execute()
    {

        $jsonFactory = $this->jsonFactory->create();
        $data = $this->getRequest()->getPostValue();
        $filter = $this->helperData->getCollectionFilter();
        if (($data && $data['lat'] != '' && $data['lng'] != '') 
        || ($data && $filter =='zipcode')) {
            if ($filter =='zipcode') {
                $data['lat'] = '';
                $data['lng'] = '';
                $data['city'] = '';
                $data['state'] = '';
                $data['country'] = '';
                $data['zipcode'] = $data['address'];
            }
           
            $s1=$this->helperData->getSellerByPostcode($data['address']);
			
            if(isset($_COOKIE['zipcode'])){
                $s2=$this->helperData->getSellerByPostcode($_COOKIE['zipcode']);
                $zip= $_COOKIE['zipcode'];
            } else{
                $s2 = '';
                $zip = '';
            }
        

            if($data['address'] != $zip){

                $cart = $this->_modelCart;
                $quoteItems = $this->checkoutSession->getQuote()->getAllVisibleItems();
				if(count($this->checkoutSession->getQuote()->getAllVisibleItems()) > 0){
                $itemModel = $this->_itemmodel;
                foreach($quoteItems as $item)
                {
                    $product = $this->_productmodel->load($item->getProductId());
                    $attrName = $product->getAttributeText('store_location');
                    if($data['address'] != $attrName){
                        $itemId = $item->getItemId();//item id of particular item
                        $quoteItem=$itemModel->load($itemId);//load particular item which you want to delete by his item id
                        $quoteItem->delete();
                    }
                }
                $quote=$this->checkoutSession->getQuote();
                if(is_numeric($quote->getId())){
                    $quoteObject = $this->quoteRepository->get($quote->getId());
                    $quoteObject->setTriggerRecollect(1);
                    $quoteObject->setIsActive(true);
                    $quoteObject->collectTotals()->save();            
                }
            }
			}
			
            // }
            $sellerstatus = 0;
            $ids = '';
            if (!empty($this->helperData->getNearestSellers())) {
                $sellerstatus = 1;
            } else {
                $ids = $this->helperData->getAllAvailablePostcode();
            }
            $ids = $this->helperData->getAllAvailablePostcode();
			
            $uniqueId = md5(($_SERVER['HTTP_USER_AGENT'] ?? "").($_SERVER['LOCAL_ADDR'] ?? "").($_SERVER['LOCAL_PORT'] ?? "").($_SERVER['REMOTE_ADDR'] ?? ""));
            $idsArray = explode(",",$this->helperData->getAllAvailablePostcode());
			
            if(in_array($data['address'],$idsArray)){
				
			$jsonAddressData = $this->jsonHelper->jsonEncode($data);
            $metadata = $this->cookieMetadata->createPublicCookieMetadata()
            ->setDuration(2147483647)
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->sessionManager->getCookieDomain());
            $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $jsonAddressData, $metadata);
            $this->httpContext->setValue(
                'hyperlocal_data',
                $jsonAddressData,
                false
            );


                $userAgentCollection = $this->useragent
                ->create()
                ->getCollection()
                ->addFieldToFilter("useragent", ["eq" => $uniqueId]);
                if ($userAgentCollection->getSize()) {
                    $userAgentCollection
                    ->setPageSize(1)
                    ->getFirstItem()
                    ->setZipcode($data['address'])
                    ->save();
                } else {
                    $this->useragent
                    ->create()
                    ->setUseragent($uniqueId)
                    ->setZipcode($data['address'])
                    ->setCreatedAt(date('Y-m-d H:i:s'))
                    ->setUpdatedAt(date('Y-m-d H:i:s'))
                    ->save();
                }
                $result = ['status'=> 1, 'msg' => __('Address Set'), 'sellerstatus' => $sellerstatus, 'ids' => $ids,'zipcode'=>$data['address']];
            } else{
                $result = ['status'=> 0, 'msg' => __('Address Set'), 'sellerstatus' => $sellerstatus, 'ids' => $ids,'zipcode'=>$data['address']];

            }
			#print_r($result);exit;
            //$result = ['status'=> 0,'ids' => $ids,'zipcode'=>$data['address']];

        } else {
            $result = ['status'=> 0, 'msg' => __('Fill Correct Address.')];
        }
        return $jsonFactory->setData($result);
    }
}
