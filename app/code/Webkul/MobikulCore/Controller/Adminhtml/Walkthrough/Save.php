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

/**
 * Class Save for walkthrough
 */
class Save extends \Webkul\MobikulCore\Controller\Adminhtml\Walkthrough
{
    /**
     * Execute Fucntion for Class Save
     *
     * @return page
     */
    public function execute()
    {
        $returnToEdit = false;
        $originalRequestData = $this->getRequest()->getPostValue();
        $walkthroughId = $originalRequestData["mobikul_walkthrough"]["id"] ?? null;
        if ($originalRequestData) {
            try {
                $walkthroughData = $originalRequestData["mobikul_walkthrough"];
                $imageName = $this->getWalkthroughName($walkthroughData);
                if (strpos($imageName, "mobikul/walkthroughs/") !== false) {
                    $walkthroughData["image"] = $imageName;
                } else {
                    $walkthroughData["image"] = "mobikul/walkthroughs/".$imageName;
                }
                $request = $this->getRequest();
                $isExistingImage = (bool) $walkthroughId;
                $walkthrough = $this->walkthroughDataFactory->create();
                if ($isExistingImage) {
                    $walkthroughData["id"] = $walkthroughId;
                }
                $walkthrough->setData($walkthroughData);
                // Save carousel image //////////////////////////////////////////////
                $walkthrough = $this->walkthroughRepository->save($walkthrough);
                $walkthroughId = $walkthrough->getId();
                $this->_getSession()->unsWalkthroughFormData();
                // Done Saving walkthrough, finish save action ////////////////////
                $this->coreRegistry->register(RegistryConstants::CURRENT_WALKTHROUGH_ID, $walkthroughId);
                $this->messageManager->addSuccess(__("You saved the Walk Through."));
                $returnToEdit = (bool) $this->getRequest()->getParam("back", false);
            } catch (\Magento\Framework\Validator\Exception $exception) {
                $messages = $exception->getMessages();
                if (empty($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setWalkthroughFormData($originalRequestData);
                $returnToEdit = true;
            } catch (\Exception $exception) {
                $this->messageManager->addException(
                    $exception,
                    __(
                        "Something went wrong while saving the Walk Through. %1",
                        $exception->getMessage()
                    )
                );
                $this->_getSession()->setWalkthroughFormData($originalRequestData);
                $returnToEdit = true;
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($returnToEdit) {
            if ($walkthroughId) {
                $resultRedirect->setPath("mobikul/walkthrough/edit", ["id"=>$walkthroughId, "_current"=>true]);
            } else {
                $resultRedirect->setPath("mobikul/walkthrough/new", ["_current"=>true]);
            }
        } else {
            $resultRedirect->setPath("mobikul/walkthrough/index");
        }
        return $resultRedirect;
    }

    /**
     * Function to get carousel Image name
     *
     * @return array
     */
    private function getWalkthroughName($walkthroughData)
    {
        if (isset($walkthroughData["image"][0]["name"])) {
            return $walkthroughData["image"] = $walkthroughData["image"][0]["name"];
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__("Please upload Walk Through image."));
        }
    }
}
