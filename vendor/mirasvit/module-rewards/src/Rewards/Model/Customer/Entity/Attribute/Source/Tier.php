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


namespace Mirasvit\Rewards\Model\Customer\Entity\Attribute\Source;

class Tier extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Mirasvit\Rewards\Model\TierFactory
     */
    private $tierFactory;
    /**
     * @var \Mirasvit\Rewards\Helper\Tier\Option
     */
    private $tierOptionHelper;
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory
     */
    protected $_eavAttrEntity;

    public function __construct(
        \Mirasvit\Rewards\Model\TierFactory $tierFactory,
        \Mirasvit\Rewards\Helper\Tier\Option $tierOptionHelper,
        \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttrEntity
    ) {
        $this->tierFactory = $tierFactory;
        $this->tierOptionHelper = $tierOptionHelper;
        $this->_eavAttrEntity = $eavAttrEntity;
    }

    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $tiers = $this->tierOptionHelper->getTierList();
            $this->_options = [];
            foreach ($tiers as $tier) {
                $this->_options[] = ['label' => $tier->getName(), 'value' => $tier->getId()];
            }
        }
        return $this->_options;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = [];
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|int $value
     * @return string|false
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();

        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'length' => 3,
                'nullable' => true,
                'comment' => $attributeCode . ' column',
            ],
        ];
    }

    /**
     * Retrieve Indexes(s) for Flat
     *
     * @return array
     */
    public function getFlatIndexes()
    {
        $indexes = [];

        $index = 'IDX_' . strtoupper($this->getAttribute()->getAttributeCode());
        $indexes[$index] = ['type' => 'index', 'fields' => [$this->getAttribute()->getAttributeCode()]];

        return $indexes;
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store
     * @return \Magento\Framework\DB\Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return $this->_eavAttrEntity->create()->getFlatUpdateSelect($this->getAttribute(), $store);
    }

    /**
     * Get a text for index option value
     *
     * @param  string|int $value
     * @return string|bool
     */
    public function getIndexOptionText($value)
    {
        $tier = $this->tierFactory->create();
        $tier->getResource()->load($tier, $value);
        if ($tier->getId()) {
            return $tier->getName();
        }

        return parent::getIndexOptionText($value);
    }
}
