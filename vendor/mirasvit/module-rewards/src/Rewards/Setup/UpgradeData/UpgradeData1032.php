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


namespace Mirasvit\Rewards\Setup\UpgradeData;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeData1032 implements UpgradeDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $table = $setup->getTable('setup_module');
        $query = 'SELECT schema_version FROM '.$table.' WHERE module = "Mirasvit_RewardsAdminUi"';
        $res = $setup->getConnection()->fetchOne($query);

        if (!$res) {
            $setup->getConnection()->insertMultiple($table, [
                ['module' => 'Mirasvit_RewardsAdminUi', 'schema_version' => '1.0.0', 'data_version' => '1.0.0'],
                ['module' => 'Mirasvit_RewardsApi', 'schema_version' => '1.0.0', 'data_version' => '1.0.0'],
                ['module' => 'Mirasvit_RewardsBehavior', 'schema_version' => '1.0.0', 'data_version' => '1.0.0'],
                ['module' => 'Mirasvit_RewardsCatalog', 'schema_version' => '1.0.0', 'data_version' => '1.0.0'],
                ['module' => 'Mirasvit_RewardsCheckout', 'schema_version' => '1.0.0', 'data_version' => '1.0.0'],
                ['module' => 'Mirasvit_RewardsCustomerAccount', 'schema_version' => '1.0.0', 'data_version' => '1.0.0'],
            ]);
        }
    }
}
