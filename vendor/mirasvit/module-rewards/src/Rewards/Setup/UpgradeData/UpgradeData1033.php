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

use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeData1033 implements UpgradeDataInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    public function __construct(
        Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $subscription = $this->eavConfig->getAttribute(Customer::ENTITY, 'rewards_subscription');
        $subscription->setData(
            'is_required',
            0
        );
        $subscription->getResource()->save($subscription);
    }
}
