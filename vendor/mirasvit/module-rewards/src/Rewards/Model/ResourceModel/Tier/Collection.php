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



namespace Mirasvit\Rewards\Model\ResourceModel\Tier;

use Mirasvit\Rewards\Api\Data\TierInterface;

/**
 * @method \Mirasvit\Rewards\Model\Tier getFirstItem()
 * @method \Mirasvit\Rewards\Model\Tier getLastItem()
 * @method \Mirasvit\Rewards\Model\ResourceModel\Tier\Collection addFieldToFilter
 * @method \Mirasvit\Rewards\Model\ResourceModel\Tier\Collection setOrder
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'tier_id'; //use in massactions

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rewards\Model\Tier', 'Mirasvit\Rewards\Model\ResourceModel\Tier');
    }

    /**
     * @param bool|false $emptyOption
     * @return array
     */
    public function toOptionArray($emptyOption = false)
    {
        $arr = [];
        if ($emptyOption) {
            $arr[0] = ['value' => 0, 'label' => __('-- Please Select --')];
        }
        /** @var \Mirasvit\Rewards\Model\Tier $item */
        foreach ($this as $item) {
            $arr[] = ['value' => $item->getId(), 'label' => $item->getName()];
        }

        return $arr;
    }

    /**
     * @param bool|false $emptyOption
     * @return array
     */
    public function getOptionArray($emptyOption = false)
    {
        $arr = [];
        if ($emptyOption) {
            $arr[0] = __('-- Please Select --');
        }
        /** @var \Mirasvit\Rewards\Model\Tier $item */
        foreach ($this as $item) {
            $arr[$item->getId()] = $item->getName();
        }

        return $arr;
    }

    /**
     * @return $this
     */
    public function joinWebsite()
    {
        $this->getSelect()->joinInner(
            ['rtw' => $this->getTable('mst_rewards_tier_website')],
            'main_table.tier_id = rtw.tier_id',
            [new \Zend_Db_Expr('GROUP_CONCAT(rtw.website_id) as website_ids')]
        )
        ->group('main_table.tier_id');

        return $this;
    }

    /**
     * @return $this
     */
    public function orderByPoints()
    {
        $this->getSelect()->order(new \Zend_Db_Expr(TierInterface::KEY_MIN_EARN_POINTS . ' ASC'));

        return $this;
    }

    /**
     * @return $this
     */
    public function addTableNameToIdFieldName()
    {
        $this->_setIdFieldName('main_table.' . $this->getIdFieldName());

        return $this;
    }

    /**
     * @return void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->joinWebsite();
    }
}
