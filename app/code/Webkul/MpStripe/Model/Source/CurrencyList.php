<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Model\Source;

class CurrencyList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Possible environment types.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => __("Please Select"),
            ],
            [
                'value' => 'chf',
                'label' => 'CHF',
            ],
            [
                'value' => 'dkk',
                'label' => 'DKK',
            ],
            [
                'value' => 'eur',
                'label' => 'EUR',
            ],
            [
                'value' => 'gbp',
                'label' => 'GBP',
            ],
            [
                'value' => 'nok',
                'label' => 'NOK',
            ],
            [
                'value' => 'sek',
                'label' => 'SEK',
            ],
            [
                'value' => 'usd',
                'label' => 'USD',
            ],
        ];
    }
}
