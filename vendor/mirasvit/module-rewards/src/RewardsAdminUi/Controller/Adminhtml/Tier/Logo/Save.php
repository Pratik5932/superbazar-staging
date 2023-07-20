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


namespace Mirasvit\RewardsAdminUi\Controller\Adminhtml\Tier\Logo;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Rewards\Model\Tier\Backend\FileProcessor;

class Save extends Action
{
    const ADMIN_RESOURCE = 'Mirasvit_Rewards::reward_points_tier';

    protected $fileProcessor;

    public function __construct(
        Context $context,
        FileProcessor $fileProcessor
    ) {
        parent::__construct($context);
        $this->fileProcessor = $fileProcessor;
    }

    /**
     * @inheritDoc
     * @since 100.1.0
     */
    public function execute()
    {
        $result = $this->fileProcessor->saveToTmp(key($_FILES));
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
