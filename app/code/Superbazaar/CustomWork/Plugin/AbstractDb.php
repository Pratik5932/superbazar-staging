<?php
namespace Superbazaar\CustomWork\Plugin;

class AbstractDb
{
    public function afterGetSize(
        \Magento\Framework\Data\Collection\AbstractDb $AbstractDb,
        $totalRecords) {

        $this->_totalRecords = $totalRecords;
        if($this->_totalRecords == 1)
        {
            $sql = $AbstractDb->getSelectCountSql();
            $this->_totalRecords = $AbstractDb->getConnection()->fetchAll($sql);
            //$this->_totalRecords = $AbstractDb->getConnection()->fetchAll($sql,$AbstractDb->_bindParams);
            $this->_totalRecords = count($this->_totalRecords);
        }
        return (int) $this->_totalRecords;

    }
}