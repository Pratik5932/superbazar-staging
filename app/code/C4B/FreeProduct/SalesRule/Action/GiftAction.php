<?php

namespace C4B\FreeProduct\SalesRule\Action;

use C4B\FreeProduct\Observer\ResetGiftItems;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule\Action\Discount;

use Psr\Log\LoggerInterface;

/**
 * Handles applying a "Buy X get Y discount(%)" type SalesRule.
 *
 * @package    C4B_FreeProduct
 * @author     Xianglan
 * @copyright  code4business Software GmbH
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class GiftAction implements Discount\DiscountInterface
{
    const ACTION = 'add_gift';

    const ITEM_OPTION_UNIQUE_ID = 'freeproduct_gift_unique_id';
    const RULE_DATA_X_SKU = 'x_sku';
    const RULE_DATA_Y_SKU = 'y_sku';
    const RULE_DATA_DISCOUNT_TYPE = 'discount_type';
    const PRODUCT_TYPE_FREEPRODUCT = 'freeproduct_gift';
    const APPLIED_FREEPRODUCT_RULE_IDS = '_freeproduct_applied_rules';
    /**
     * @var Discount\DataFactory
     */
    private $discountDataFactory;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var ResetGiftItems
     */
    private $resetGiftItems;
    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * @param Discount\DataFactory $discountDataFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ResetGiftItems $resetGiftItems
     * @param LoggerInterface $logger
     */
    public function __construct(Discount\DataFactory $discountDataFactory,
                                ProductRepositoryInterface $productRepository,
                                \Magento\Catalog\Model\ProductFactory $productFactory,
                                ResetGiftItems $resetGiftItems,
                                LoggerInterface $logger,
                                \Magento\Checkout\Model\Cart $cart,
                                \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData)
    {
        $this->discountDataFactory = $discountDataFactory;
        $this->productRepository = $productRepository;
        $this->resetGiftItems = $resetGiftItems;
        $this->logger = $logger;
        $this->cart = $cart;
        $this->productFactory = $productFactory;
        $this->discountData = $discountData;
    }

    /**
     * Add gift product to quote, if not yet added
     *
     * @author Xianglan K.
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     * @return Discount\Data
     */
    public function calculate($rule, $item, $qty)
    {
        $appliedRuleIds = $item->getAddress()->getData(static::APPLIED_FREEPRODUCT_RULE_IDS);

        if ($item->getAddress()->getAddressType() != Address::TYPE_SHIPPING
            || ($appliedRuleIds != null && isset($appliedRuleIds[$rule->getId()])))
        {
            return $this->getDiscountData($item);
        }

        $x_skus = explode(',', $rule->getData(static::RULE_DATA_X_SKU));
        $skus = explode(',', $rule->getData(static::RULE_DATA_Y_SKU));
        $discount_type = $rule->getData(static::RULE_DATA_DISCOUNT_TYPE);
        $cart_items = $this->cart->getItems();
        $discount_amount = $rule->getDiscountAmount();
        $isRuleAdded = false;
        $discount_data = array(0, 0, 0, 0);
        $discountDataObject = $this->discountDataFactory->create();
        $y_qtys = array();
        foreach($cart_items as $cart_item)
        {
            foreach($skus as $y_sku)
            {
                if($cart_item->getSKU() == $y_sku)
                {
                    $y_qtys[$y_sku] = $cart_item->getQty();
                    break;
                }
            }
        }

        $total_x_qty = 0;
        foreach($x_skus as $x_sku)
        {
            foreach($cart_items as $cart_item)
            {
                if($x_sku == $cart_item->getSKU())
                {
                    $total_x_qty += $cart_item->getQty();
                    break;
                }
            }
        }

        $step = $rule->getDiscountStep();
        $base_discount_qty = floor($total_x_qty / $step);
        $discount_qty = $base_discount_qty;
        foreach($cart_items as $cart_item)
        {
            foreach($skus as $sku)
            {
                if($cart_item->getSKU() == $sku)
                {
//                    $discount_qty = ($base_discount_qty > $y_qtys[$sku]) ? $y_qtys[$sku] : $base_discount_qty;
                    try
                    {
                        if($y_qtys[$sku] == 0)
                        {
                            continue;
                        }
                        $discount_qty = ($base_discount_qty > $y_qtys[$sku]) ? $y_qtys[$sku] : $base_discount_qty;
                        $product = $this->productFactory->create();
                        $item_info = $product->loadByAttribute('sku', $sku);
			$itemPrice = ($item_info->getSpecialPrice() > 0) ? $item_info->getSpecialPrice() : $item_info->getPrice();
                        $baseItemPrice = $item_info->getBasePrice();
                        $itemOriginalPrice = $item_info->getOriginalPrice();
                        $baseItemOriginalPrice = $item_info->getBaseOriginalPrice();
                        if($discount_type == '%')
                        {
                            $discount_data[0] += $itemPrice * $discount_qty * $discount_amount / 100;
                            $discount_data[1] += $baseItemPrice * $discount_qty * $discount_amount / 100;
                            $discount_data[2] += $itemOriginalPrice * $discount_qty * $discount_amount / 100;
                            $discount_data[3] += $baseItemOriginalPrice * $discount_qty * $discount_amount / 100;
                        }
                        else if($discount_type == '$')
                        {
                            $discount_data[0] += $discount_qty * $discount_amount;
                            $discount_data[1] += $discount_qty * $discount_amount;
                            $discount_data[2] += $discount_qty * $discount_amount;
                            $discount_data[3] += $discount_qty * $discount_amount;
                        }
                        $y_qtys[$sku] = 0;
                        // $rule->getDiscountAmount()
                        // $quoteItem = $item->getQuote()->addProduct($this->getGiftProduct($sku), $discount_qty);
                        // $item->getQuote()->setItemsCount($item->getQuote()->getItemsCount() + 1);
                        // $item->getQuote()->setItemsQty((float)$item->getQuote()->getItemsQty() + $quoteItem->getQty());
                        // $this->resetGiftItems->reportGiftItemAdded();
                        $isRuleAdded = true;
                        $base_discount_qty -= $discount_qty;
                        if($discount_qty == 0)
                        {
                            if ($isRuleAdded)
                            {
                                $this->addAppliedRuleId($rule->getRuleId(), $item->getAddress());
                            }
                    // print_r($discount_data);exit();
                            $discountDataObject->setAmount($discount_data[0]);
                            $discountDataObject->setBaseAmount($discount_data[1]);
                            $discountDataObject->setOriginalAmount($discount_data[2]);
                            $discountDataObject->setBaseOriginalAmount($discount_data[3]);
                            $item->setDiscountAmount($discount_data[0]);
                            $item->getQuote()->setDiscountAmount($discountDataObject);
                            return $this->discountDataFactory->create([
                                'amount' => $item->getDiscountAmount(),
                                'baseAmount' => $item->getBaseDiscountAmount(),
                                'originalAmount' => $item->getOriginalDiscountAmount(),
                                'baseOriginalAmount' => $item->getBaseOriginalDiscountAmount()
                            ]);
                        }
                    } catch (\Exception $e)
                    {
                        $this->logger->error(
                            sprintf('Exception occurred while adding gift product %s to cart. Rule: %d, Exception: %s', implode(',', $skus), $rule->getId(), $e->getMessage()),
                            [__METHOD__]
                        );
                    }
                }
            }
        }
        
        if ($isRuleAdded)
        {
            $this->addAppliedRuleId($rule->getRuleId(), $item->getAddress());
        }

        $discountDataObject->setAmount($discount_data[0]);
        $discountDataObject->setBaseAmount($discount_data[1]);
        $discountDataObject->setOriginalAmount($discount_data[2]);
        $discountDataObject->setBaseOriginalAmount($discount_data[3]);

        $item->setDiscountAmount($discount_data[0]);
        $item->getQuote()->setDiscountAmount($discountDataObject);
        return $this->discountDataFactory->create([
            'amount' => $item->getDiscountAmount(),
            'baseAmount' => $item->getBaseDiscountAmount(),
            'originalAmount' => $item->getOriginalDiscountAmount(),
            'baseOriginalAmount' => $item->getBaseOriginalDiscountAmount()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function fixQuantity($qty, $rule)
    {
        return $qty;
    }

    /**
     * @param int $ruleId
     * @param Address $address
     */
    protected function addAppliedRuleId(int $ruleId, Address $address)
    {
        $appliedRules = $address->getData(static::APPLIED_FREEPRODUCT_RULE_IDS);

        if ($appliedRules == null)
        {
            $appliedRules = [];
        }

        $appliedRules[$ruleId] = $ruleId;

        $address->setData(static::APPLIED_FREEPRODUCT_RULE_IDS, $appliedRules);
    }

    /**
     * Get and prepare the gift product
     *
     * @param string $sku
     * @return ProductInterface|Product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getGiftProduct(string $sku): ProductInterface
    {
        /** @var Product $product */
        $product = $this->productRepository->get($sku);
        /**
         * Makes it unique, to avoid merging
         * @see \Magento\Quote\Model\Quote\Item::representProduct
         */
        $product->addCustomOption(static::ITEM_OPTION_UNIQUE_ID, uniqid());
        $product->addCustomOption(CartItemInterface::KEY_PRODUCT_TYPE, static::PRODUCT_TYPE_FREEPRODUCT);

        return $product;
    }

    /**
     * No discount is changed by GiftAction, but the existing has to be preserved
     *
     * @param AbstractItem $item
     * @return Discount\Data
     */
    protected function getDiscountData(AbstractItem $item, $discount_data = array(0,0,0,0)): Discount\Data
    {
        return $this->discountDataFactory->create([
            'amount' => $item->getDiscountAmount() + $discount_data[0],
            'baseAmount' => $item->getBaseDiscountAmount() + $discount_data[1],
            'originalAmount' => $item->getOriginalDiscountAmount() + $discount_data[2],
            'baseOriginalAmount' => $item->getBaseOriginalDiscountAmount() + $discount_data[3]
        ]);
    }
}