<?php
namespace Mexbs\AdditionalPromotions\Model\Plugin\Rule\Metadata;

class ValueProvider
{
    public function afterGetMetadataValues(
        \Magento\SalesRule\Model\Rule\Metadata\ValueProvider $subject,
        $metaDataValues
    ){
        $apSimpleActionOptions =
        [
            [
                'label' => 'Discount steps: First N items, next M items, next K items ...',
                'value' =>
                [
                    [
                        'label' => __('Percent Discount: First N items with A% discount, next M items with B% ...'),
                        'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\FirstNNextMAfterKPercentDiscount::SIMPLE_ACTION
                    ],
                    [
                        'label' => __('Fixed Discount: First N items with A$ discount, next M items with B$ ...'),
                        'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\FirstNNextMAfterKFixedDiscount::SIMPLE_ACTION
                    ],
                    [
                        'label' => __('Fixed Price: First N items for A$, next M items for B$ ...'),
                        'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\FirstNNextMAfterKFixedPriceDiscount::SIMPLE_ACTION
                    ]
                ]
            ],
            [
                'label' => 'Get Y$ for each X$ spent',
                'value' =>
                    [
                        [
                            'label' => __('Get Y$ for each X$ spent on all items matching ...'),
                            'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetYForEachXSpent::SIMPLE_ACTION
                        ],
                        [
                            'label' => __('Get Y$ for each X$ spent, on up to N items in cart matching ...'),
                            'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetYForEachXSpentUpToN::SIMPLE_ACTION
                        ]
                    ]
            ],
            [
                'label' => 'BOGO: Buy X get different Y',
                'value' =>
                    [
                        [
                            'label' => __('Percent Discount: Buy X get N of different Y with Z% discount'),
                            'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyXGetNOfYPercentDiscount::SIMPLE_ACTION
                        ],
                        [
                            'label' => __('Fixed Discount: Buy X get N of different Y with Z$ discount'),
                            'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyXGetNOfYFixedDiscount::SIMPLE_ACTION
                        ],
                        [
                            'label' => __('Fixed Price: Buy X get N of different Y for Z$'),
                            'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyXGetNOfYFixedPriceDiscount::SIMPLE_ACTION
                        ],
                    ]
            ],
            [
                'label' => 'Extended BOGO: Buy A + B + C + ... get different D',
                'value' =>
                    [
                        [
                            'label' => __('Percent Discount: Buy A + B + C ... get N of different D with Z% discount'),
                            'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyABCGetNOfDPercentDiscount::SIMPLE_ACTION
                        ],
                        [
                            'label' => __('Fixed Discount: Buy A + B + C ... get N of different D with Z$ discount'),
                            'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyABCGetNOfDFixedDiscount::SIMPLE_ACTION
                        ],
                        [
                            'label' => __('Fixed Price: Buy A + B + C ... get N of different D for Z$'),
                            'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyABCGetNOfDFixedPriceDiscount::SIMPLE_ACTION
                        ],
                    ]
            ],
            [
                'label' => 'Bundle: Buy A + B + C + D for ...',
                'value' =>
                [
                    [
                        'label' => __('Percent Discount: N items of type A + M items of type B + ..., with Z% discount'),
                        'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\ProductsSetPercentDiscount::SIMPLE_ACTION
                    ],
                    [
                        'label' => __('Fixed Discount: N items of type A + M items of type B + ..., with Z$ discount'),
                        'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\ProductsSetFixedDiscount::SIMPLE_ACTION
                    ],
                    [
                        'label' => __('Fixed Price: N items of type A + M items of type B + ..., for Z$'),
                        'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\ProductsSetFixedPriceDiscount::SIMPLE_ACTION
                    ]
                ]
            ],
            [
                'label' => 'Category tier: Get each group of N items for ...',
                'value' =>
                [
                    [
                        'label' => __('Percent Discount: Group of N items with Z% discount'),
                        'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\EachGroupOfNPercentDiscount::SIMPLE_ACTION
                    ],
                    [
                        'label' => __('Fixed Discount: Group of N items with Z$ discount'),
                        'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\EachGroupOfNFixedDiscount::SIMPLE_ACTION
                    ],
                    [
                        'label' => __('Fixed Price: Group of N items for Z$'),
                        'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\EachGroupOfNFixedPriceDiscount::SIMPLE_ACTION
                    ]
                ]
            ],
            [
                'label' => 'N + M / Each N:  N + M on items of same type, after M added to cart for full price',
                'value' =>
                    [
                        [
                            'label' => __('Percent Discount: Buy N, get M subsequent items with Z% discount, after M added'),
                            'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetEachNAfterMPercentDiscount::SIMPLE_ACTION
                        ],
                        [
                            'label' => __('Fixed Discount: Buy N, get M subsequent items with Z$ discount, after M added'),
                            'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetEachNAfterMFixedDiscount::SIMPLE_ACTION
                        ],
                        [
                            'label' => __('Fixed Price: Buy N, get M subsequent items for Z$, after M added'),
                            'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetEachNAfterMFixedPriceDiscount::SIMPLE_ACTION
                        ]
                    ]
            ],
            [
                'label' => 'All after M added: Discount on all items of same type, after M added to cart for full price',
                'value' =>
                    [
                        [
                            'label' => __('Percent Discount: Get all items with Z% discount, after M added'),
                            'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetAllAfterMPercentDiscount::SIMPLE_ACTION
                        ],
                        [
                            'label' => __('Fixed Discount: Get all items with Z$ discount, after M added'),
                            'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetAllAfterMFixedDiscount::SIMPLE_ACTION
                        ],
                        [
                            'label' => __('Fixed Price: Get all items for Z$, after M added'),
                            'value' =>  \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetAllAfterMFixedPriceDiscount::SIMPLE_ACTION
                        ]
                    ]
            ]
        ];

        if(isset($metaDataValues['actions']['children']['simple_action']['arguments']['data']['config']['options'])){
            if(!is_array($metaDataValues['actions']['children']['simple_action']['arguments']['data']['config']['options'])){
                $metaDataValues['actions']['children']['simple_action']['arguments']['data']['config']['options'] = [];
            }
            $metaDataValues['actions']['children']['simple_action']['arguments']['data']['config']['options'] =
                array_merge($metaDataValues['actions']['children']['simple_action']['arguments']['data']['config']['options'],
                    $apSimpleActionOptions);
        }

        return $metaDataValues;
    }
}