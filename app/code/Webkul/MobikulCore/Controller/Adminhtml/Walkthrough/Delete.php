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
 * Class Delete for Walkthrough
 */
class Delete extends \Webkul\MobikulCore\Controller\Adminhtml\Walkthrough
{
    /**
     * Execute Function for Class Delete
     *
     * @return page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addError(__("Walk Through image could not be deleted."));
            return $resultRedirect->setPath("mobikul/walkthrough/index");
        }
        $walkthroughId = $this->initCurrentWalkthrough();
        if (!empty($walkthroughId)) {
            try {
                $this->walkthroughRepository->deleteById($walkthroughId);
                $this->messageManager->addSuccess(__("Walk Through image has been deleted."));
            } catch (\Exception $exception) {
                $this->messageManager->addError($exception->getMessage());
            }
        }
        return $resultRedirect->setPath("mobikul/walkthrough/index");
    }

    /**
     * Fucntion to Init current carousel Image
     *
     * @return array
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
