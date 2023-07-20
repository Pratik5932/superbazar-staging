<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Marketplace
 * @author Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Model\ResourceModel\VendorAttributeMapping;

use Webkul\Marketplace\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\Marketplace\Model\VendorAttributeMapping::class,
            \Webkul\Marketplace\Model\ResourceModel\VendorAttributeMapping::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}
