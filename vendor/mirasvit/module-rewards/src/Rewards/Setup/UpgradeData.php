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


namespace Mirasvit\Rewards\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Mirasvit\Rewards\Model\TierFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var UpgradeDataInterface[]
     */
    private $pool;

    public function __construct(
        UpgradeData\UpgradeData106 $upgrade106,
        UpgradeData\UpgradeData1016 $upgrade1016,
        UpgradeData\UpgradeData1017 $upgrade1017,
        UpgradeData\UpgradeData1019 $upgrade1019,
        UpgradeData\UpgradeData1020 $upgrade1020,
        UpgradeData\UpgradeData1029 $upgrade1029,
        UpgradeData\UpgradeData1030 $upgrade1030,
        UpgradeData\UpgradeData1031 $upgrade1031,
        UpgradeData\UpgradeData1032 $upgrade1032,
        UpgradeData\UpgradeData1033 $upgrade1033,
        UpgradeData\UpgradeData1037 $upgrade1037
    ) {
        $this->pool = [
            '1.0.6'  => $upgrade106,
            '1.0.16' => $upgrade1016,
            '1.0.17' => $upgrade1017,
            '1.0.19' => $upgrade1019,
            '1.0.20' => $upgrade1020,
            '1.0.29' => $upgrade1029,
            '1.0.30' => $upgrade1030,
            '1.0.31' => $upgrade1031,
            '1.0.32' => $upgrade1032,
            '1.0.33' => $upgrade1033,
            '1.0.37' => $upgrade1037,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        foreach ($this->pool as $version => $upgrade) {
            if (version_compare($context->getVersion(), $version) < 0) {
                $upgrade->upgrade($setup, $context);
            }
        }

        $setup->endSetup();
    }
}
