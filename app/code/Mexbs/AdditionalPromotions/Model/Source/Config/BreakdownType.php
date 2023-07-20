<?php
namespace Mexbs\AdditionalPromotions\Model\Source\Config;

use Magento\Framework\Data\OptionSourceInterface;

class BreakdownType implements OptionSourceInterface
{
    const TYPE_LABELS = "labels";
    const TYPE_LABELS_AND_PRODUCT_NAMES = "labels_and_product_names";
    const TYPE_COMPREHENSIVE = "comprehensive";

    public function toOptionArray()
    {
        return [
            [
                'value' => self::TYPE_LABELS,
                'label' => __('Rules Labels')
            ],
            [
                'value' => self::TYPE_LABELS_AND_PRODUCT_NAMES,
                'label' => __('Rules Labels and Product Names')
            ]
        ];
    }
}
