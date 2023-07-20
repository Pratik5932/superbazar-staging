<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulMp
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\MobikulMp\Controller\Chat;

use Magento\Framework\App\Action\Context;
use Webkul\MobikulCore\Helper\Data as HelperData;
use Webkul\MobikulCore\Helper\Catalog as HelperCatalog;
use Magento\Store\Model\App\Emulation;

/**
 * MpMobikul API chat controller
 */
class SellerList extends \Webkul\MobikulApi\Controller\ApiController
{
    /**
     * $_customerFactory.
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * $dir
     *
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $dir;

    /**
     * $seller
     *
     * @var \Webkul\Marketplace\Model\SellerFactory
     */
    protected $seller;

    /**
     * $emulate
     *
     * @var Magento\Store\Model\App\Emulation
     */
    protected $emulate;

    /**
     * $baseDir
     *
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $baseDir;

    /**
     * $deviceToken
     *
     * @var \Webkul\MobikulCore\Model\DeviceTokenFactory
     */
    protected $deviceToken;

    /**
     * $helperCatalog
     *
     * @var HelperCatalog
     */
    protected $helperCatalog;
    
    /**
     * Json Helper
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * Constrcut function for class Seller Lists
     *
     * @param Context                                      $context         context
     * @param Emulation                                    $emulate         emulate
     * @param HelperData                                   $helper          helper
     * @param HelperCatalog                                $helperCatalog   helperCatalog
     * @param \Webkul\Marketplace\Model\SellerFactory      $seller          seller
     * @param \Magento\Framework\Json\Helper\Data          $jsonHelper      jsonHelper
     * @param \Magento\Framework\Filesystem\DirectoryList  $baseDir         baseDir
     * @param \Webkul\MobikulCore\Model\DeviceTokenFactory $deviceToken     deviceToken
     * @param \Magento\Customer\Model\CustomerFactory      $customerFactory customerFactory
     */
    public function __construct(
        Context $context,
        Emulation $emulate,
        HelperData $helper,
        HelperCatalog $helperCatalog,
        \Webkul\Marketplace\Model\SellerFactory $seller,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Filesystem\DirectoryList $baseDir,
        \Webkul\MobikulCore\Model\DeviceTokenFactory $deviceToken,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->seller          = $seller;
        $this->helper          = $helper;
        $this->emulate         = $emulate;
        $this->baseDir         = $baseDir;
        $this->deviceToken     = $deviceToken;
        $this->helperCatalog   = $helperCatalog;
        $this->customerFactory = $customerFactory;
        parent::__construct($helper, $context, $jsonHelper);
    }

    /**
     * Execute notify admin.
     *
     * @return array
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "SELLERLIST".$this->width.$this->websiteId.$this->storeId;
            $cacheString .= $this->mFactor.$this->customerToken.$this->sellerId;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $adminEmail  = $this->helper->getConfigData('mobikulmp/admin/email');
            $width       = $this->width*$this->mFactor;
            $height      = ($this->width/2)*$this->mFactor;
            if ($adminEmail) {
                $customer = $this->customerFactory->create()->setWebsiteId($this->websiteId)->loadByEmail($adminEmail);
                $customerIdNotToBeIncluded = [];
                $customerIdNotToBeIncluded[] = 0;
                $customerIdNotToBeIncluded[] = $customer->getId();
                $androidTokenCollection = $this->deviceToken
                    ->create()
                    ->getCollection()
                    ->addFieldToFilter('customer_id', ['nin' => $customerIdNotToBeIncluded]);
                $logoArray = [];
                $sellerCollection = $this->seller
                    ->create()
                    ->getCollection()
                    ->addFieldToSelect('is_seller')
                    ->addFieldToSelect('seller_id')
                    ->addFieldToFilter('is_seller', 1);
                $sellerIdArray = [];
                foreach ($sellerCollection as $value) {
                    $sellerIdArray[] = $value->getSellerId();
                    $logoArray[$value->getSellerId()] = $value->getLogoPic()!=''?$value->getLogoPic():"noimage.png";
                }
                $sellerList = [];
                foreach ($androidTokenCollection as $token) {
                    if (!in_array($token->getCustomerId(), $sellerIdArray)) {
                        continue;
                    }
                    $eachSeller = [];
                    $isExist = 0;
                    foreach ($sellerList as $key => $value) {
                        if ($value['customerId'] == $token->getCustomerId()) {
                            $sellerList[$key]['token'] = $value['token'].','.$token->getToken();
                            $isExist = 1;
                            break;
                        }
                    }
                    if ($isExist == 0) {
                        $eachSeller['customerId']    = $token->getCustomerId();
                        $eachSeller['customerToken'] = $this->helper->getTokenByCustomerDetails(
                            null,
                            null,
                            $token->getCustomerId()
                        );
                        $eachSeller['token'] = $token->getToken();
                        $collection = $this->customerFactory->create()
                            ->getCollection()
                            ->addAttributeToSelect('firstname')
                            ->addAttributeToSelect('lastname')
                            ->addAttributeToSelect('entity_id')
                            ->addFieldToFilter('entity_id', $token->getCustomerId());
                        foreach ($collection as $item) {
                            $eachSeller['name'] = $item->getFirstname().' '.$item->getLastname();
                            $eachSeller['email'] = $item->getEmail();
                        }
                        $basePath = $this->baseDir->getPath("media").'/avatar/'.$logoArray[$token->getCustomerId()];
                        $newPath  = $this->baseDir->getPath(
                            "media"
                        )."/mobikulresized/avatar/".$width."x".$height."/".$logoArray[$token->getCustomerId()];
                        $this->helperCatalog->resizeNCache($basePath, $newPath, $width, $height);
                        $eachSeller['profileImage'] = $this->helper->getUrl(
                            "media"
                        )."mobikulresized/avatar/".$width."x".$height."/".$logoArray[$token->getCustomerId()];
                        $sellerList[] = $eachSeller;
                    }
                }
                $this->returnArray['apiKey'] = $this->helper->getConfigData("mobikul/notification/apikey");
                $this->returnArray['success'] = true;
                $this->returnArray['sellerList'] = $sellerList;
                $this->emulate->stopEnvironmentEmulation($environment);
                return $this->getJsonResponse($this->returnArray);
            } else {
                $this->returnArray['message'] = __('Unauthorised Access');
                $this->emulate->stopEnvironmentEmulation($environment);
                return $this->getJsonResponse($this->returnArray);
            }
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->checkNGenerateEtag($cacheString);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->helper->printLog(
                'MpMobikul Exception log for class: '.get_class($this).' : '.$e->getMessage(),
                (array) $e->getTrace()
            );
            $this->returnArray['message'] = __($e->getMessage());
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        $this->wholeData = $this->getRequest()->getParams();
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->eTag          = $this->wholeData["eTag"]          ?? "";
            $this->width         = $this->wholeData["width"]         ?? 1000;
            $this->websiteId     = $this->wholeData['websiteId']     ?? 0;
            $this->storeId       = $this->wholeData['storeId']       ?? 0;
            $this->mFactor       = $this->wholeData['mFactor']       ?? 1;
            $this->customerToken = $this->wholeData["customerToken"] ?? '';
            $this->sellerId      = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->sellerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("As the customer you are requesting does not exist, so you need to logout.")
                );
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
