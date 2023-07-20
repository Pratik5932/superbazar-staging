<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Controller\Adminhtml\Walkthrough;

use Webkul\MobikulCore\Controller\RegistryConstants;
use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\MobikulCore\Api\Data\WalkthroughInterface;

/**
 * Class Edit for Walkthrough
 */
class Edit extends \Webkul\MobikulCore\Controller\Adminhtml\Walkthrough
{
    /**
     * Execute Fucntion for Class Edit
     *
     * @return page
     */
    public function execute()
    {
        $walkthroughId = $this->initCurrentWalkthrough();
        $isExistingWalkthrough = (bool)$walkthroughId;
        if ($isExistingWalkthrough) {
            try {
                $baseTmpPath = "";
                $target = $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ).$baseTmpPath;
                $walkthroughData = [];
                $walkthroughData["mobikul_walkthrough"] = [];
                $walkthrough = null;
                $walkthrough = $this->walkthroughRepository->getById($walkthroughId);
                $result = $walkthrough->getData();
                if (count($result)) {
                    $walkthroughData["mobikul_walkthrough"] = $result;
                    $walkthroughData["mobikul_walkthrough"]["image"] = [];
                    $walkthroughData["mobikul_walkthrough"]["image"][0] = [];
                    $walkthroughData["mobikul_walkthrough"]["image"][0]["name"] = $result["image"];
                    $walkthroughData["mobikul_walkthrough"]["image"][0]["url"] = $target.$result["image"];
                    $filePath = $this->mediaDirectory->getAbsolutePath($baseTmpPath).$result["image"];
                    if ($this->fileDriver->isFile($filePath)) {
                        $walkthroughData["mobikul_walkthrough"]["image"][0]["size"] =
                        $this->fileHelper->getFileSize($filePath);
                    } else {
                        $walkthroughData["mobikul_walkthrough"]["image"][0]["size"] = 0;
                    }
                    $walkthroughData["mobikul_walkthrough"][WalkthroughInterface::ID] = $walkthroughId;
                    $this->_getSession()->setWalkthroughFormData($walkthroughData);
                } else {
                    $this->messageManager->addError(__("Requested walkthrough doesn't exist"));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath("mobikul/walkthrough/index");
                    return $resultRedirect;
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException($e, __("Something went wrong while editing the walkthrough."));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath("mobikul/walkthrough/index");
                return $resultRedirect;
            }
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu("Webkul_MobikulCore::walkthrough");
        if ($isExistingWalkthrough) {
            $resultPage->getConfig()->getTitle()->prepend(__("Edit Item with id %1", $walkthroughId));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__("New Walk Through"));
        }
        return $resultPage;
    }

    /**
     * Fucntion to init Current walkthrough image for Class MassEnable
     *
     * @return int
     */
    protected function initCurrentWalkthrough()
    {
        $walkthroughId = (int)$this->getRequest()->getParam("id");
        if ($walkthroughId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_WALKTHROUGH_ID, $walkthroughId);
        }
        return $walkthroughId;
    }
}
