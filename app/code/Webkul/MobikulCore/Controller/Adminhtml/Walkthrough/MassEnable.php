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
 * Class MassEnable for walkthrough
 */
class MassEnable extends \Webkul\MobikulCore\Controller\Adminhtml\Walkthrough
{
    /**
     * Execute Fucntion for Class MassEnable
     *
     * @return page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $walkthroughsUpdated = 0;
        $coditionArr = [];
        foreach ($collection->getAllIds() as $walkthroughId) {
            $currentWalkthrough = $this->walkthroughRepository->getById($walkthroughId);
            $walkthroughData = $currentWalkthrough->getData();
            if (count($walkthroughData)) {
                $condition = "`id`=".$walkthroughId;
                array_push($coditionArr, $condition);
                $walkthroughsUpdated++;
            }
        }
        $coditionData = implode(" OR ", $coditionArr);
        $collection->setWalkthroughData($coditionData, ["status"=>1]);
        if ($walkthroughsUpdated) {
            $this->messageManager->addSuccess(__("A total of %1 record(s) were enabled.", $walkthroughsUpdated));
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
