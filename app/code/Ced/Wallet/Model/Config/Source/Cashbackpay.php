<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Wallet
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Wallet\Model\Config\Source;

/**
 * Class Wallet
 * @package Ced\Wallet\Model\Config\Source
 */
class Cashbackpay implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options array
     *
     * @var array
     */
    protected $_options;

    /**
     * Return options array
     *
     * @param boolean $isMultiselect
     * @param string|array $actons
     * @return array
     */
    public function toOptionArray($isMultiselect = false)
    {
        $options = [
                       /* ['value' => 'interval', 'label' => __('on Interval')],*/
                        ['value'=> 'one_time','label'=>__('One Time')]
                        
                    ];
        return $options;
    }
}
