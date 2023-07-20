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
 * MpMobikul API chat controller.
 */
class NotifyAdmin extends \Webkul\MobikulApi\Controller\ApiController
{
    /**
     * $customerFactory
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
     * $emulate
     *
     * @var Emulation
     */
    protected $emulate;

    /**
     * $deviceToken
     *
     * @var \Webkul\MobikulApi\Model\DeviceTokenFactory
     */
    protected $deviceToken;

    /**
     * $helperData
     *
     * @var \Webkul\MobikulCore\Helper\Data
     */
    protected $helper;

    /**
     * $helperData
     *
     * @var \Webkul\MobikulCore\Helper\Catalog
     */
    protected $helperCatalog;
    
    /**
     * Construct Function for class Notify Admin
     *
     * @param Context                                     $context         context
     * @param Emulation                                   $emulate         emulate
     * @param HelperData                                  $helper          helper
     * @param HelperCatalog                               $helperCatalog   helperCatalog
     * @param \Magento\Framework\Json\Helper\Data         $jsonHelper      jsonHelper
     * @param \Webkul\MobikulMp\Helper\Data               $mobikulMpHelper mobikulMpHelper
     * @param \Webkul\MobikulApi\Model\DeviceTokenFactory $deviceToken     deviceToken
     * @param \Magento\Customer\Model\CustomerFactory     $customerFactory customerFactory
     */
    public function __construct(
        Context $context,
        Emulation $emulate,
        HelperData $helper,
        HelperCatalog $helperCatalog,
        \Webkul\MobikulMp\Helper\Data $mobikulMpHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\MobikulCore\Model\DeviceTokenFactory $deviceToken,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        $this->helper          = $helper;
        $this->emulate         = $emulate;
        $this->jsonHelper      = $jsonHelper;
        $this->deviceToken     = $deviceToken;
        $this->helperCatalog   = $helperCatalog;
        $this->mobikulMpHelper = $mobikulMpHelper;
        $this->customerFactory = $customerFactory;
        $this->curl = $curl;
        parent::__construct($helper, $context, $jsonHelper);
    }

    /**
     * Execute notify admin.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $adminEmail = $this->helper->getConfigData('mobikulmp/admin/email');
            $customer = $this->customerFactory->create()->setWebsiteId($this->websiteId)->loadByEmail($adminEmail);
            $androidTokenCollection = $this->deviceToken
                ->create()
                ->getCollection()
                ->addFieldToFilter('customer_id', $customer->getId());
            foreach ($androidTokenCollection as $token) {
                $sellerTokenCollection = $this->deviceToken
                    ->create()
                    ->getCollection()
                    ->addFieldToFilter("customer_id", $this->sellerId);
                $sellerTokens = [];
                foreach ($sellerTokenCollection as $each) {
                    $sellerTokens[] = $each->getToken();
                }
                $message = [
                    "id"               => $this->sellerId,
                    "customerToken"    => $this->customerToken,
                    "name"             => $this->sellerName,
                    "body"             => $this->sellerMessage,
                    "sound"            => "default",
                    "title"            => "New Message from ".$this->sellerName,
                    "apiKey"           => $this->helper->getConfigData("mobikul/notification/apikey"),
                    "tokens"           => implode(",", $sellerTokens),
                    "message"          => $this->sellerMessage,
                    "notificationType" => "chatNotification"
                ];
                $url = 'https://fcm.googleapis.com/fcm/send';
                $authKey = $this->helper->getConfigData("mobikul/notification/apikey");
                $headers = [
                    'Authorization: key='.$authKey,
                    'Content-Type: application/json',
                ];
                $error = 0;
                $errorMsg = [];
                $fields = [
                    "to"                => $token->getToken(),
                    "data"              => $message,
                    "priority"          => "high",
                    "content_available" => true,
                    "time_to_live"      => 30,
                    "delay_while_idle"  => true
                ];
                if ($token->getOs() == "ios") {
                    $fields["notification"] = $message;
                }
                $this->curl->setHeaders($headers);
                $this->curl->post($url, json_encode($fields));
                $result = $this->curl->getBody();
                if ($this->mobikulMpHelper->isJson($result)) {
                    $result = json_decode($result, true);
                    if ($result["success"] == 0 && $result["failure"] == 1) {
                        $token->delete();
                    }
                }
            }
            $this->returnArray['success'] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray['success'] = 0;
            $this->returnArray['message'] = __("Invalid Request.");
            $this->emulate->stopEnvironmentEmulation($environment);
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
            $this->sellerMessage = $this->wholeData['message']       ?? '';
            $this->websiteId     = $this->wholeData['websiteId']     ?? '';
            $this->sellerName    = $this->wholeData['sellerName']    ?? '';
            $this->storeId       = $this->wholeData['storeId']       ?? '';
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
