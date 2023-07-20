<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_OrderDeliveryDate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OrderDeliveryDate\Plugin;

use Bss\OrderDeliveryDate\Helper\Data as HelperData;

/**
 * Class GetHelperObject
 * @package Bss\OrderDeliveryDate\Plugin
 */
class GetHelperObject
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * GetHelperObject constructor.
     * @param HelperData $data
     */
    public function __construct(
        HelperData $data
    ) {
        $this->helperData = $data;
    }

    /**
     * @return HelperData
     */
    public function afterGetHelperObject()
    {
        return $this->helperData;
    }
}