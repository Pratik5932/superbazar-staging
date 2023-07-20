<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rewards
 * @version   3.0.57
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\RewardsAdminUi\Controller\Adminhtml\Tier;

use Magento\Ui\Component\MassAction\Filter;
use Mirasvit\Rewards\Model\ResourceModel\Tier\CollectionFactory;

class MassDelete extends \Mirasvit\RewardsAdminUi\Controller\Adminhtml\Tier
{
    private $filter;
    private $collectionFactory;

    public function __construct(
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Mirasvit\Rewards\Model\TierFactory $tierFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($tierFactory, $registry, $storeManager, $context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $ids = [];

        if ($this->getRequest()->getParam(Filter::SELECTED_PARAM)) {
            $ids = $this->getRequest()->getParam(Filter::SELECTED_PARAM);
        }

        if (!$ids) {
            $tierCollection = $this->collectionFactory->create();
            $tierCollection->addTableNameToIdFieldName();
            $collection = $this->filter->getCollection($tierCollection);
            $ids = $collection->getAllIds();
        }

        if ($ids && is_array($ids)) {
            try {
                foreach ($ids as $id) {
                    /** @var \Mirasvit\Rewards\Model\Tier $tier */
                    $tier = $this->tierFactory->create()
                        ->setIsMassDelete(true);
                    $tier->getResource()->load($tier, $id);
                    $tier->getResource()->delete($tier);
                }
                $this->messageManager->addSuccessMessage(
                    __(
                        'Total of %1 record(s) were successfully deleted', count($ids)
                    )
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        } else {
            $this->messageManager->addErrorMessage(__('Please select Tier(s)'));
        }
        $this->_redirect('*/*/index');
    }
}
