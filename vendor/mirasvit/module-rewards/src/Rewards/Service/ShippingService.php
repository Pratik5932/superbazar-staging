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



namespace Mirasvit\Rewards\Service;

use Magento\Quote\Model\Quote\Address;
use Magento\Tax\Model\Config as TaxConfig;
use Mirasvit\Rewards\Model\Config;

class ShippingService
{
    /**
     * @var TaxConfig
     */
    private $taxConfig;
    /**
     * @var Config
     */
    private $config;
    public function __construct(
        TaxConfig $taxConfig,
        Config $config
    ) {
        $this->taxConfig = $taxConfig;
        $this->config = $config;
    }

    /**
     * @var float
     */
    private $shippingAmount;

    /**
     * @return float
     */
    public function getShippingAmount()
    {
        return $this->shippingAmount;
    }

    /**
     * @return float
     */
    public function getShippingAmountExclTax($shippingAddress)
    {
        return $this->getBaseShippingPrice($shippingAddress);
    }

    /**
     * @param float $shippingAmount
     *
     * @return $this
     */
    public function setShippingAmount($shippingAmount)
    {
        $this->shippingAmount = $shippingAmount;

        return $this;
    }

    /**
     * @param Address|\Magento\Quote\Model\Quote\Address\Total $shippingAddress
     * @return float
     */
    public function getBaseSpendShippingPrice($shippingAddress)
    {
        if ($this->config->getGeneralIsIncludeTaxSpending()) {
            return $shippingAddress->getBaseShippingInclTax();
        } else {
            return $this->getBaseShippingPrice($shippingAddress);
        }
    }

    /**
     * @param Address|\Magento\Quote\Model\Quote\Address\Total $shippingAddress
     * @return float
     */
    public function getBaseEarnShippingPrice($shippingAddress)
    {
        if ($this->config->getGeneralIsIncludeTaxEarning()) {
            return $shippingAddress->getBaseShippingInclTax();
        } else {
            return $this->getBaseShippingPrice($shippingAddress);
        }
    }

    /**
     * @param Address|\Magento\Quote\Model\Quote\Address\Total $shippingAddress
     * @return float
     */
    private function getBaseShippingPrice($shippingAddress)
    {
        if ($this->taxConfig->shippingPriceIncludesTax()) {
            return $shippingAddress->getBaseShippingInclTax() - $shippingAddress->getBaseShippingTaxAmount();
        } else {
            return $shippingAddress->getBaseShippingAmount();
        }
    }

    /**
     * @param Address|Address\Total $shippingAddress
     * @return float
     */
    public function getSpendShippingPrice($shippingAddress)
    {
        if ($this->config->getGeneralIsIncludeTaxSpending()) {
            return $shippingAddress->getShippingInclTax();
        } else {
            return $this->getShippingPrice($shippingAddress);
        }
    }

    /**
     * @param Address $shippingAddress
     * @return float
     */
    public function getEarnShippingPrice($shippingAddress)
    {
        if ($this->config->getGeneralIsIncludeTaxEarning()) {
            return $shippingAddress->getShippingInclTax();
        } else {
            return $this->getShippingPrice($shippingAddress);
        }
    }

    /**
     * @param Address $shippingAddress
     * @return float
     */
    private function getShippingPrice($shippingAddress)
    {
        if ($this->taxConfig->shippingPriceIncludesTax()) {
            return $shippingAddress->getShippingInclTax() - $shippingAddress->getShippingTaxAmount();
        } else {
            return $shippingAddress->getShippingAmount();
        }
    }
}
