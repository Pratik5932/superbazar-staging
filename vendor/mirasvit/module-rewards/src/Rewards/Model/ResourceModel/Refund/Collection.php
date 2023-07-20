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



namespace Mirasvit\Rewards\Model\ResourceModel\Refund;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method \Mirasvit\Rewards\Model\Refund getFirstItem()
 * @method \Mirasvit\Rewards\Model\Refund getLastItem()
 * @method \Mirasvit\Rewards\Model\ResourceModel\Refund\Collection addFieldToFilter
 * @method \Mirasvit\Rewards\Model\ResourceModel\Refund\Collection setOrder
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'refund_id'; //use in massactions

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rewards\Model\Refund', 'Mirasvit\Rewards\Model\ResourceModel\Refund');
    }
}
