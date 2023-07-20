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



namespace Mirasvit\Rewards\Model\Cron;

use Magento\Framework\Filesystem;
use Mirasvit\RewardsAdminUi\Model\ResourceModel\Report\PointsFactory;

class Report extends AbstractCron
{
    private $reportPointsFactory;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        PointsFactory $reportPointsFactory,
        Filesystem $filesystem
    ) {
        $this->reportPointsFactory = $reportPointsFactory;
        parent::__construct($filesystem);
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Zend_Db_Statement_Exception
     * @return void
     */
    protected function execute()
    {
        try {
            $this->reportPointsFactory->create()->aggregate();
            echo "Statistics was successfully updated";
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            echo "Database error. Please contact developers";
        }
    }
}
