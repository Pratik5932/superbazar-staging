<?php
/**
 * Copyright © 2015 Ren. All rights reserved.
 */
namespace Ren\Pushapp\Model\ResourceModel;

/**
 * Pushapp resource
 */
class Pushapp extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('pushapp_pushapp', 'id');
    }

  
}
