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
 * Class MassDisable for walkthrough
 */
class MassDisable extends \Webkul\MobikulCore\Controller\Adminhtml\Walkthrough
{
    /**
     * Execute Fucntion for Class MassDisable
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
        $collection->setWalkthroughData($coditionData, ["status" => 0]);
        if ($walkthroughsUpdated) {
            $this->messageManager->addSuccess(__("A total of %1 record(s) were disabled.", $walkthroughsUpdated));
        }
        return $resultRedirect->setPath("mobikul/walkthrough/index");
    }

    /**
     * Function to check if the controller isallowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::walkthrough");
    }
}
