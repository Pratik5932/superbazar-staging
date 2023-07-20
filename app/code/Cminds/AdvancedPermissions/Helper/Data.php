<?php

namespace Cminds\AdvancedPermissions\Helper;

/**
 * Class Data
 *
 * @package Cminds\AdvancedPermissions\Helper
 */
class Data
{
    /**
     * Make Category array compatible with jQuery jsTree component.
     *
     * @param array $resources
     *
     * @return array
     */
    public function mapCategories(array $categories)
    {
        $output = [];
        foreach ($categories as $category) {
            $item = [];
            $item['attr']['data-id'] = $category['id'];
            $item['data'] = $category['text'];
            $item['children'] = [];
            if (isset($category['children'])) {
                $item['state'] = 'open';
                $item['children'] = $this->mapCategories($category['children']);
            }
            $output[] = $item;
        }
        return $output;
    }
}
