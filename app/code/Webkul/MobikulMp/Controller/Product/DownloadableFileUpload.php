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
namespace Webkul\MobikulMp\Controller\Product;

/**
 * Class Downladablefileupload for uploading fiile for downloadable product
 *
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class DownloadableFileUpload extends AbstractProduct
{
    private $files = [];

    /**
     * Execute function for class DownloadableFileUpload
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->customerSession->setCustomerId($this->sellerId);
            if (!$this->marketplaceHelper->isSeller()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("invalid seller")
                );
            }
            $destPath = '';
            if ($this->type == 'links') {
                $destPath = $this->link->getBaseTmpPath();
            } elseif ($this->type == 'samples') {
                $destPath = $this->sample->getBaseTmpPath();
            } elseif ($this->type == 'link_samples') {
                $destPath = $this->link->getBaseSampleTmpPath();
            }
            $fileUploader = $this->fileUploaderFactory->create(
                ['fileId' => 'links']
            );
            $resultData = $this->fileHelper->uploadFromTmp($destPath, $fileUploader);
            $this->checkUploadFile($resultData);
            if (isset($resultData['file'])) {
                $relativePath = rtrim($destPath, '/') . '/' . ltrim($resultData['file'], '/');
                $this->databaseStorage->saveFile($relativePath);
            }
            $this->returnArray["name"]     = $resultData["name"]     ?? "";
            $this->returnArray["type"]     = $resultData["type"]     ?? "";
            $this->returnArray["size"]     = $resultData["size"]     ?? 0;
            $this->returnArray["file"]     = $resultData["file"]     ?? "";
            $this->returnArray["error"]    = $resultData["error"]    ?? "";
            $this->returnArray["tmp_name"] = $resultData["tmp_name"] ?? "";
            $this->returnArray["message"]  = __('File has been successfully uploaded');
            $this->returnArray["success"]  = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->returnArray["message"] = $e->getMessage();
            return $this->getJsonResponse(
                $this->returnArray
            );
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray, 1);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    public function checkUploadFile($resultData = false)
    {
        if (!$resultData) {
            throw new \Magento\Framework\Exception\LocalizedException('File can not be uploaded.');
        }
    }

    /**
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     *
     * @return json | void
     */
    protected function verifyRequest()
    {
        $this->files = (array) $this->getRequest()->getFiles();
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData && !empty($this->files)) {
            $this->storeId       = $this->wholeData["storeId"]       ?? 0;
            $this->type          = $this->wholeData["type"]          ?? "";
            $this->customerToken = $this->wholeData["customerToken"] ?? '';
            $this->sellerId      = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->sellerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            } elseif ($this->sellerId != 0) {
                $this->customerSession->setCustomerId($this->sellerId);
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
