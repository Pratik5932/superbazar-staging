<?php
namespace Mexbs\AdditionalPromotions\Model\Source\SalesRule;

use Magento\Framework\Data\OptionSourceInterface;

class YesNoConfig implements OptionSourceInterface
{
    const CONFIG = 2;
    const YES = 1;
    const NO = 0;

    public function toOptionArray()
    {
        return [
            [
                'value' => 2,
                'label' => __('Use Config Value')
            ],
            [
                'value' => 1,
                'label' => __('Yes')
            ],
            [
                'value' => 0,
                'label' => __('No')
            ]
        ];
    }
}
