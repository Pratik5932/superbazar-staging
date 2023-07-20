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

class IntegrationType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const STRIPE_CONNECT = '1';
    const STRIPE_CONNECT_CUSTOM = '2';

    /**
     * Possible environment types.
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                [
                    'value' => '',
                    'label' => __("Please Select"),
                ],
                [
                    'value' => self::STRIPE_CONNECT,
                    'label' => __('Stripe Connect'),
                ],
                [
                    'value' => self::STRIPE_CONNECT_CUSTOM,
                    'label' => __('Stripe Connect with Custom Accounts'),
                ]
            ];
        }
        return $this->_options;
    }
}
