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
 * Class UploadProductImage for Uploading product image from vendor's end
 *
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class UploadProductImage extends AbstractProduct
{

    /**
     * Execute function for class UploadProductImage
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $target = $this->mediaDirectory->getAbsolutePath(
                $this->mediaConfig->getBaseTmpMediaPath()
            );
            $fileUploader = $this->fileUploaderFactory->create(
                ['fileId' => 'image']
            );
            $fileUploader->setAllowedExtensions(
                ['gif', 'jpg', 'png', 'jpeg']
            );
            $fileUploader->setFilesDispersion(true);
            $fileUploader->setAllowRenameFiles(true);
            $this->returnArray = $fileUploader->save($target);
            unset($this->returnArray['tmp_name']);
            unset($this->returnArray['path']);
            $this->returnArray['url'] = $this->mediaConfig->getTmpMediaUrl($this->returnArray['file']);
            $this->returnArray['file'] = $this->returnArray['file'].'.tmp';
            $this->returnArray["message"] = __('Image has been successfully uploaded');
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray, 1);
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
        $files = (array) $this->getRequest()->getFiles();
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData && !empty($files)) {
            $this->storeId       = $this->wholeData["storeId"]       ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? '';
            $this->customerId    = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            } elseif ($this->customerId != 0) {
                $this->customerSession->setCustomerId($this->customerId);
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
