<?php

namespace Cminds\AdvancedPermissions\Model\ResourceModel\Report\Bestsellers;

use Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection as BestselleresCollection;

class Collection extends BestselleresCollection {

    /**
     * get bestsellers collection period
     *
     * @return string
     */
    public function getPeriod()
    {
        return $this->_period;
    }

    /**
     * get bestsellers collection from
     *
     * @return string
     */
    public function getFrom()
    {
        return $this->_from;
    }

    /**
     * get bestsellers collection to
     *
     * @return string
     */
    public function getTo()
    {
        return $this->_to;
    }
}


