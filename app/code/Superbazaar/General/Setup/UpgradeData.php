<?php

namespace Superbazaar\General\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @param EavSetup $eavSetupFactory
     */
    public function __construct(
        \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * Upgrades customer_eav_attribute table for validate_rules to set limit on character for first and lastname
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $entityAttributes = [
            'customer_address' => [
                'firstname' => [
                    'validate_rules' => '{"max_text_length":32,"min_text_length":1}'
                ],
                'lastname' => [
                    'validate_rules' => '{"max_text_length":32,"min_text_length":1}'
                ],
            ],
            'customer' => [
                'firstname' => [
                    'validate_rules' => '{"max_text_length":32,"min_text_length":1}'
                ],
                'lastname' => [
                    'validate_rules' => '{"max_text_length":32,"min_text_length":1}'
                ],
            ],
        ];
        $this->upgradeAttributes($entityAttributes, $customerSetup);
        $setup->endSetup();
    }

    protected function upgradeAttributes(array $entityAttributes, \Magento\Customer\Setup\CustomerSetup $customerSetup)
    {
        foreach ($entityAttributes as $entityType => $attributes) {
            foreach ($attributes as $attributeCode => $attributeData) {
                $attribute = $customerSetup->getEavConfig()->getAttribute($entityType, $attributeCode);
                foreach ($attributeData as $key => $value) {
                    $attribute->setData($key, $value);
                }
                $attribute->save();
            }
        }
    }
}