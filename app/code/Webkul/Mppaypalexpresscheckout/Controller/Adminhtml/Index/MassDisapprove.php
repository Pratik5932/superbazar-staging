<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mppaypalexpresscheckout
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mppaypalexpresscheckout\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Mppaypalexpresscheckout\Model\ResourceModel\Mppaypalexpresscheckout\CollectionFactory;

/**
 * Class MassDisapprove.
 */
class MassDisapprove extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Webkul\Mppaypalexpresscheckout\Helper\Data
     */
    private $helper;

     /**
      * @var \Webkul\Mppaypalexpresscheckout\Helper\Email
      */
    private $mailHelper;

    /**
     * @param Context                                      $context
     * @param Filter                                       $filter
     * @param \Magento\Framework\Stdlib\DateTime\DateTime  $date
     * @param CollectionFactory                            $collectionFactory
     * @param \Webkul\Mppaypalexpresscheckout\Helper\Data  $helper
     * @param \Webkul\Mppaypalexpresscheckout\Helper\Email $mailHelper
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        CollectionFactory $collectionFactory,
        \Webkul\Mppaypalexpresscheckout\Helper\Data $helper,
        \Webkul\Mppaypalexpresscheckout\Helper\Email $mailHelper
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->date = $date;
        $this->helper = $helper;
        $this->mailHelper = $mailHelper;
        parent::__construct($context);
    }

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        try {
            $status = \Webkul\Mppaypalexpresscheckout\Model\Mppaypalexpresscheckout::STATUS_DISABLED;
            $collection = $this->filter->getCollection(
                $this->collectionFactory->create()
            );
            foreach ($collection as $item) {
                $this->unApprove($item, $status);
            }

            $this->messageManager->addSuccess(__(
                'A total of %1 record(s) have been disapproved.',
                $collection->getSize()
            ));
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Controller_Adminhtml_Index_MassDisapprove execute : ".$e->getMessage());
            $this->messageManager->addError($e->getMessage());
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(
            ResultFactory::TYPE_REDIRECT
        );
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * unapprove
     *
     * @param object $item
     * @param boolean $status
     * @return void
     */
    private function unApprove($item, $status)
    {
        $id = $item->getId();
        $item->setStatus($status);
        $item->setUpdatedAt($this->date->gmtDate());
        $item->save();

        $this->mailHelper->sendDetailsStatusMailToSeller($id);
    }

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Marketplace::seller');
    }
}
