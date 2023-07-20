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

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var array<string,mixed>
     */
    private $pool;

    public function __construct(
        UpgradeSchema\UpgradeSchema101 $upgrade101,
        UpgradeSchema\UpgradeSchema102 $upgrade102,
        UpgradeSchema\UpgradeSchema103 $upgrade103,
        UpgradeSchema\UpgradeSchema104 $upgrade104,
        UpgradeSchema\UpgradeSchema105 $upgrade105,
        UpgradeSchema\UpgradeSchema107 $upgrade107,
        UpgradeSchema\UpgradeSchema108 $upgrade108,
        UpgradeSchema\UpgradeSchema109 $upgrade109,
        UpgradeSchema\UpgradeSchema1010 $upgrade1010,
        UpgradeSchema\UpgradeSchema1011 $upgrade1011,
        UpgradeSchema\UpgradeSchema1012 $upgrade1012,
        UpgradeSchema\UpgradeSchema1013 $upgrade1013,
        UpgradeSchema\UpgradeSchema1014 $upgrade1014,
        UpgradeSchema\UpgradeSchema1015 $upgrade1015,
        UpgradeSchema\UpgradeSchema1016 $upgrade1016,
        UpgradeSchema\UpgradeSchema1018 $upgrade1018,
        UpgradeSchema\UpgradeSchema1021 $upgrade1021,
        UpgradeSchema\UpgradeSchema1022 $upgrade1022,
        UpgradeSchema\UpgradeSchema1023 $upgrade1023,
        UpgradeSchema\UpgradeSchema1024 $upgrade1024,
        UpgradeSchema\UpgradeSchema1025 $upgrade1025,
        UpgradeSchema\UpgradeSchema1026 $upgrade1026,
        UpgradeSchema\UpgradeSchema1027 $upgrade1027,
        UpgradeSchema\UpgradeSchema1028 $upgrade1028,
        UpgradeSchema\UpgradeSchema1030 $upgrade1030,
        UpgradeSchema\UpgradeSchema1032 $upgrade1032,
        UpgradeSchema\UpgradeSchema1034 $upgrade1034,
        UpgradeSchema\UpgradeSchema1035 $upgrade1035,
        UpgradeSchema\UpgradeSchema1036 $upgrade1036,
        UpgradeSchema\UpgradeSchema1038 $upgrade1038,
        UpgradeSchema\UpgradeSchema1039 $upgrade1039,
        UpgradeSchema\UpgradeSchema1040 $upgrade1040
    ) {
        $this->pool = [
            '1.0.1'  => $upgrade101,
            '1.0.2'  => $upgrade102,
            '1.0.3'  => $upgrade103,
            '1.0.4'  => $upgrade104,
            '1.0.5'  => $upgrade105,
            '1.0.7'  => $upgrade107,
            '1.0.8'  => $upgrade108,
            '1.0.9'  => $upgrade109,
            '1.0.10' => $upgrade1010,
            '1.0.11' => $upgrade1011,
            '1.0.12' => $upgrade1012,
            '1.0.13' => $upgrade1013,
            '1.0.14' => $upgrade1014,
            '1.0.15' => $upgrade1015,
            '1.0.16' => $upgrade1016,
            '1.0.18' => $upgrade1018,
            '1.0.21' => $upgrade1021,
            '1.0.22' => $upgrade1022,
            '1.0.23' => $upgrade1023,
            '1.0.24' => $upgrade1024,
            '1.0.25' => $upgrade1025,
            '1.0.26' => $upgrade1026,
            '1.0.27' => $upgrade1027,
            '1.0.28' => $upgrade1028,
            '1.0.30' => $upgrade1030,
            '1.0.32' => $upgrade1032,
            '1.0.34' => $upgrade1034,
            '1.0.35' => $upgrade1035,
            '1.0.36' => $upgrade1036,
            '1.0.38' => $upgrade1038,
            '1.0.39' => $upgrade1039,
            '1.0.40' => $upgrade1040
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
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
