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


namespace Mirasvit\Rewards\Helper;

use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface as CurrencyHelper;

class Calculation extends AbstractHelper
{
    const ZERO_VALUE = 0.00099;
    /**
     * @var CurrencyHelper
     */
    private $currencyHelper;
    /**
     * @var CurrencyFactory
     */
    private $dirCurrencyFactory;

    public function __construct(
        CurrencyFactory $dirCurrencyFactory,
        CurrencyHelper $currencyHelper,
        Context $context
    ) {
        parent::__construct($context);

        $this->currencyHelper     = $currencyHelper;
        $this->dirCurrencyFactory = $dirCurrencyFactory;
    }

    /**
     * @param float $amount
     * @param Currency|string $fromCurrency
     * @param Currency|string $toCurrency
     * @param null|string|bool|int|\Magento\Framework\App\ScopeInterface $store
     *
     * @return float
     */
    public function convertToCurrency($amount, $fromCurrency, $toCurrency, $store)
    {
        if (!$fromCurrency instanceof Currency) {
            /** @var Currency $fromCurrency */
            $fromCurrency = $this->currencyHelper->getCurrency($store, $fromCurrency);
        }
        if (!$toCurrency instanceof Currency) {
            /** @var Currency $toCurrency */
            $toCurrency = $this->currencyHelper->getCurrency($store, $toCurrency);
        }

        try {
            $converted = $fromCurrency->convert($amount, $toCurrency);
        } catch (\Exception $e) {
            $converted = $this->calcCurrencyRate($amount, $fromCurrency, $toCurrency);
        }

        return $converted;
    }

    /**
     * @param float $amount
     * @param Currency $fromCurrency
     * @param Currency $toCurrency
     * @return float
     */
    public function calcCurrencyRate($amount, $fromCurrency, $toCurrency)
    {
        if ($fromCurrency->getCurrencyCode() == $toCurrency->getCurrencyCode()) {
            return $amount;
        }

        $currencyModel = $this->dirCurrencyFactory->create();
        $rates = $currencyModel->getCurrencyRates(
            $fromCurrency->getCurrencyCode(), $toCurrency->getCurrencyCode()
        );
        if (!count($rates) || !isset($rates[$toCurrency->getCurrencyCode()])) {
            $rates = $currencyModel->getCurrencyRates(
                $toCurrency->getCurrencyCode(), $fromCurrency->getCurrencyCode()
            );
            $currencyRate = 1 / $rates[$fromCurrency->getCurrencyCode()];
            $rates[$toCurrency->getCurrencyCode()] = $currencyRate;
        }
        $rate = $rates[$toCurrency->getCurrencyCode()];

        return $this->currencyHelper->round($amount * $rate);
    }
}
