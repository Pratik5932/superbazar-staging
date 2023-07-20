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

/**
 * Class MassDelete for walkthrough
 */
class MassDelete extends \Webkul\MobikulCore\Controller\Adminhtml\Walkthrough
{
    /**
     * Execute Fucntion for Class MassDelete
     *
     * @return page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $walkthroughsDeleted = 0;
        foreach ($collection->getAllIds() as $walkthroughId) {
            if (!empty($walkthroughId)) {
                try {
                    $this->walkthroughRepository->deleteById($walkthroughId);
                    $walkthroughsDeleted++;
                } catch (\Exception $exception) {
                    $this->messageManager->addError($exception->getMessage());
                }
            }
        }
        if ($walkthroughsDeleted) {
            $this->messageManager->addSuccess(__("A total of %1 record(s) were deleted.", $walkthroughsDeleted));
        }
        return $resultRedirect->setPath("mobikul/walkthrough/index");
    }

    /**
     * Fucntion to check if the controller is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::walkthrough");
    }
}
