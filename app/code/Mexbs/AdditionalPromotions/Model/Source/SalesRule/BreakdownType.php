<?php
namespace Mexbs\AdditionalPromotions\Model\Source\SalesRule;

use Magento\Framework\Data\OptionSourceInterface;

class BreakdownType implements OptionSourceInterface
{
    const TYPE_CONFIG = "config";

    public function toOptionArray()
    {
        return [
            [
                'value' => self::TYPE_CONFIG,
                'label' => __('Use Config Value'),
                'available_for_non_ap_action' => true
            ],
            [
                'value' => \Mexbs\AdditionalPromotions\Model\Source\Config\BreakdownType::TYPE_LABELS,
                'label' => __('Rules Labels'),
                'available_for_non_ap_action' => true
            ],
            [
                'value' => \Mexbs\AdditionalPromotions\Model\Source\Config\BreakdownType::TYPE_LABELS_AND_PRODUCT_NAMES,
                'label' => __('Rules Labels and Product Names'),
                'available_for_non_ap_action' => true
            ],
            [
                'value' => \Mexbs\AdditionalPromotions\Model\Source\Config\BreakdownType::TYPE_COMPREHENSIVE,
                'label' => __('Comprehensive description'),
                'available_for_non_ap_action' => false
            ]
        ];
    }
}
