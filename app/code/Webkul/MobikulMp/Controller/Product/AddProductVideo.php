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

use Magento\Framework\File\Uploader;
use Magento\Framework\App\Filesystem\DirectoryList as FilesystemDirectoryList;

/**
 * Class AddProductVideo for adding video to product
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class AddProductVideo extends AbstractProduct
{

    /**
     * Execute function for class AddProductVideo
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment   = $this->emulate->startEnvironmentEmulation($this->storeId);
            $baseTmpMediaPath = $this->mediaConfig->getBaseTmpMediaPath();
            $remoteImageUrl = $this->helper->validate(
                $this->wholeData,
                "remote_image"
            ) ? $this->wholeData["remote_image"] : '';
            //Replacement of basename function
            $baseFileName = '';
            $imageUrl = str_replace('\\', '/', $remoteImageUrl);
            $parts = explode('/', $imageUrl);
            if (is_array($parts)) {
                $baseFileName = end($parts);
            }
            // $baseFileName = basename($remoteImageUrl);
            $localFileName = Uploader::getCorrectFileName($baseFileName);
            $localTmpFileName = Uploader::getDispretionPath($localFileName).DIRECTORY_SEPARATOR.$localFileName;
            $localFileMediaPath = $baseTmpMediaPath.($localTmpFileName);
            $localUniqueFileMediaPath = $this->getNewFileName($localFileMediaPath);
            $this->saveRemoteImage($remoteImageUrl, $localUniqueFileMediaPath);
            $localFileFullPath = $this->getDestinationFileAbsolutePath($localUniqueFileMediaPath);
            $this->imageAdapter->validateUploadFile($localFileFullPath);
            $this->returnArray['videoData'] = $this->appendResultSaveRemoteImage($localUniqueFileMediaPath);
            $this->returnArray["success"]    = true;
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
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId       = $this->wholeData["storeId"]       ?? 0;
            $this->width         = $this->wholeData["width"]         ?? 1000;
            $this->mFactor       = $this->wholeData["mFactor"]       ?? 1;
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

    /**
     * Function to get new file name
     *
     * @param string $localFilePath localFilePath
     *
     * @return string
     */
    protected function getNewFileName($localFilePath)
    {
        $destinationFile = $this->getDestinationFileAbsolutePath($localFilePath);
        $fileName = Uploader::getNewFileName($destinationFile);
        $fileInfo = $this->fileIo->getPathInfo($localFilePath);
        return $fileInfo['dirname'].DIRECTORY_SEPARATOR.$fileName;
    }

    /**
     * Function to get absolute destination path
     *
     * @param string $localTmpFile localTmpFile
     *
     * @return string
     */
    protected function getDestinationFileAbsolutePath($localTmpFile)
    {
        $mediaDirectory = $this->fileSystem->getDirectoryRead(FilesystemDirectoryList::MEDIA);
        $pathToSave = $mediaDirectory->getAbsolutePath();
        return $pathToSave.$localTmpFile;
    }

    /**
     * Function to save remote image
     *
     * @param string $fileName fileName
     *
     * @return mixed
     */
    protected function appendResultSaveRemoteImage($fileName)
    {
        $fileInfo = $this->fileIo->getPathInfo($fileName);
        $tmpFileName = Uploader::getDispretionPath($fileInfo['basename']).DIRECTORY_SEPARATOR.$fileInfo['basename'];
        $result['name'] = $fileInfo['basename'];
        $result['type'] = $this->imageAdapter->getMimeType();
        $result['error'] = 0;
        $result['size'] = $this->fileHelper->getFileSize($this->getDestinationFileAbsolutePath($fileName));
        $result['url'] = $this->mediaConfig->getTmpMediaUrl($tmpFileName);
        $result['file'] = $tmpFileName;

        return $result;
    }

    /**
     * Function to save remote image.
     *
     * @param string $fileUrl       fileUrl
     * @param string $localFilePath localFilePath
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return void
     */
    protected function saveRemoteImage($fileUrl, $localFilePath)
    {
        $this->curl->setConfig(['header' => false]);
        $this->curl->write('GET', $fileUrl);
        $image = $this->curl->read();
        if (empty($image)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Could not get preview image information. Please check your connection and try again.')
            );
        }
        $this->fileUtility->saveFile($localFilePath, $image);
    }
}
