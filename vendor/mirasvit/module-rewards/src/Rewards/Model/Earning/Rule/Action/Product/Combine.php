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



namespace Mirasvit\Rewards\Model\Earning\Rule\Action\Product;

class Combine extends \Magento\SalesRule\Model\Rule\Condition\Product\Combine
{
    /**
     * @return array
     */
    private function getProductAttributes()
    {
        return [
            'type_id'          => __('Product Type'),
            'image'            => __('Base Image'),
            'thumbnail'        => __('Thumbnail'),
            'small_image'      => __('Small Image'),
            'image_size'       => __('Base Image Size (bytes)'),
            'thumbnail_size'   => __('Thumbnail Size (bytes)'),
            'small_image_size' => __('Small Image Size (bytes)'),
            'php'              => __('PHP Condition'),
            'price'            => __('Base Price'),
            'final_price'      => __('Final Price'),
            'special_price'    => __('Special Price'),
        ];
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $attributes = [];
        foreach ($this->getProductAttributes() as $code => $label) {
            $attributes[] = [
                'value' => \Mirasvit\Rewards\Model\Earning\Rule\Condition\Product::class . '|' . $code,
                'label' => $label,
            ];
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                ['label' => __('Additional Product Attribute'), 'value' => $attributes],
            ]
        );

        return $conditions;
    }

    /**
     * @inheritDoc
     */
    public function asHtmlRecursive()
    {
        $this->setJsFormObject($this->getFormName() . 'rule_actions_fieldset');

        return parent::asHtmlRecursive();
    }
}
