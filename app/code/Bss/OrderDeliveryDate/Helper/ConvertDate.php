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
// @codingStandardsIgnoreFile
namespace Bss\OrderDeliveryDate\Helper;

/**
 * Class ConvertDate
 *
 * @package Bss\OrderDeliveryDate\Helper
 */
class ConvertDate extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * ConvertDate constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
    }
    /**
     * @inheritdoc
     */
    public function scopeDate($scope = null, $date = null, $includeTime = false)
    {
        $timezone = $this->scopeConfig->getValue(
            \Magento\Directory\Helper\Data::XML_PATH_DEFAULT_TIMEZONE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scope
        );
        $date = new \DateTime(is_numeric($date) ? '@' . $date : $date, new \DateTimeZone($timezone));
        if (!$includeTime) {
            $date->setTime(0, 0, 0);
        }
        return $date;
    }
}
