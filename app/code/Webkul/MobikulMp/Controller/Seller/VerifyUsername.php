<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulB2B
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\MobikulMp\Controller\Seller;

/**
 * Abstract Class AbstractSeller for adding all the required dependencies used for customer features.
 *
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */

class VerifyUsername extends AbstractSeller
{
    /**
     * Seller prodile Url
     *
     * @var String
     */
    private $profileUrl;

    public function execute()
    {

        try {
            $this->verifyRequest();
            $cacheString = "VERIFYUSERNAME".$this->storeId.$this->profileUrl;
            if ($this->mobikulHelper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $collection = $this->sellerModel->getCollection()
            ->addFieldToFilter(
                'shop_url',
                $this->profileUrl
            );
            if ($collection->getSize()) {
                $this->returnArray["success"] = false;
                $this->returnArray["message"] = __("Not Available");
            } else {
                $this->returnArray["success"] = true;
                $this->returnArray["message"] = __("Available");
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->checkNGenerateEtag($cacheString);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Function verify Request to authenticate the request
     * Authenticates the request and logs the result for invalid requests
     *
     * @return Json
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->profileUrl = $this->wholeData["profileUrl"] ?? "";
            if (!$this->profileUrl && $this->profileUrl != "") {
                $this->returnArray["otherError"] = "profileUrlNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Please provide Profile Url id...")
                );
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
