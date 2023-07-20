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


namespace Mirasvit\Rewards\Plugin;

use Magento\Framework\Api\Filter;

/**
 * @package Mirasvit\Rewards\Plugin
 */
class DataProviderGroupFilter
{
    /**
     * @param \Mirasvit\Report\Ui\DataProvider $dataProvider
     * @param callable                         $proceed
     * @param Filter                           $filter
     * @param string                           $group
     * @return null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAddFilter(\Mirasvit\Report\Ui\DataProvider $dataProvider, $proceed, Filter $filter, $group = '')
    {
        if (strpos($filter->getField(), 'customer_group_id') !== false && $filter->getValue()) {
            if ($filter->getValue() == '%-1%') {
                $filter->setValue('0');// "not logged in" group
            }
        }
        $result = $proceed($filter, $group);

        return $result;
    }
}