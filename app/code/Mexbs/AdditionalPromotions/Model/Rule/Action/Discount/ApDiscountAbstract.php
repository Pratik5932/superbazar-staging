<?php
namespace Mexbs\AdditionalPromotions\Model\Rule\Action\Discount;

abstract class ApDiscountAbstract extends \Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount
{
    protected $logger;
    protected $apHelper;

    public function __construct(
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory $discountDataFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Mexbs\AdditionalPromotions\Helper\Data $apHelper,
        \Mexbs\AdditionalPromotions\Logger\Logger $logger
    ) {
        $this->apHelper = $apHelper;
        $this->logger = $logger;
        parent::__construct(
            $validator,
            $discountDataFactory,
            $priceCurrency
        );
    }

    protected function _isPricesMatchExpected($itemPrices, $expectedItemPrices){
        $pricesKeys = [
            'price',
            'base_price',
            'original_price',
            'base_original_price',
        ];
        foreach($pricesKeys as $priceKeys){
            if(!isset($itemPrices[$priceKeys])
                || !isset($expectedItemPrices[$priceKeys])
                || ($itemPrices[$priceKeys] != $expectedItemPrices[$priceKeys])){
                return false;
            }
        }
        return true;
    }

    protected function _getItemSkus($items){
        $itemSkus = [];
        foreach($items as $item){
            $itemSkus[] = $item->getSku();
        }
        return $itemSkus;
    }

    protected function _logErrorPricesDontMatchExpected($itemPrices, $expectedItemPrices, $itemSkus){
        $this->logger->addError(sprintf(
            "Prices don't match expected prices (in %s). Prices are: %s. Expected prices are: %s. Item SKUs are: %s.",
            get_class($this),
            print_r($itemPrices, 1),
            print_r($expectedItemPrices, 1),
            print_r($itemSkus, 1)
        ));
    }
}