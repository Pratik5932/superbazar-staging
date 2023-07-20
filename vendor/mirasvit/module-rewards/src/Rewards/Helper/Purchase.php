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

use Magento\Checkout\Model\CartFactory;
use Magento\Framework\App\Helper\Context;
use Mirasvit\Rewards\Model\Config;
use Mirasvit\Rewards\Model\PurchaseFactory;
use Mirasvit\Rewards\Model\ResourceModel\Purchase\CollectionFactory;

class Purchase extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $config;

    private $purchaseFactory;

    private $cartFactory;

    private $purchaseCollectionFactory;

    private $context;


    public function __construct(
        Config $config,
        PurchaseFactory $purchaseFactory,
        CartFactory $cartFactory,
        CollectionFactory $purchaseCollectionFactory,
        Context $context
    ) {
        $this->config                    = $config;
        $this->purchaseFactory           = $purchaseFactory;
        $this->cartFactory               = $cartFactory;
        $this->purchaseCollectionFactory = $purchaseCollectionFactory;
        $this->context                   = $context;

        parent::__construct($context);
    }

    /**
     * @param int|\Magento\Quote\Model\Quote $quoteId
     * @param bool                           $isCreateNew
     *
     * @return bool|\Mirasvit\Rewards\Model\Purchase
     */
    public function getByQuote($quoteId, $isCreateNew = true)
    {
        $purchase = false;
        $quote    = false;

        if (is_object($quoteId)) {
            $quote   = $quoteId;

            if (!$quote->getBoltParentQuoteId()) {
                $quoteId = $quote->getId();
            } else {
                $quoteId = $quote->getBoltParentQuoteId();
            }
        }

        if (!$quoteId) {
            return false;
        }

        $collection = $this->purchaseCollectionFactory->create()
            ->addFieldToFilter('quote_id', $quoteId);

        if ($collection->count()) {
            $purchase = $collection->getFirstItem();

            if ($quote) {
                $purchase->setQuote($quote);
            }
        } elseif ($isCreateNew) {
            $purchase = $this->purchaseFactory->create()->setQuoteId($quoteId);

            if ($quote) {
                $purchase->setQuote($quote);
            }

            $purchase->save();
        }

        return $purchase;
    }

    /**
     * @param \Magento\Sales\Model\Order|\Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return bool|\Mirasvit\Rewards\Model\Purchase
     */
    public function getByOrder($order)
    {
        $purchase = null;

        /** start compatibility with Aheadgroups_Ordereditor */
        if ($order->getId()) {
            $collection = $this->purchaseCollectionFactory->create()
                ->addFieldToFilter('order_id', $order->getId());
            if ($collection->count()) {
                $purchase = $collection->getFirstItem();
            }
        }
        /** end compatibility with Aheadgroups_Ordereditor */

        if (!$purchase) {
            if (!$purchase = $this->getByQuote($order->getQuoteId(), false)) {
                return false;
            }
        }

        if (!$purchase->getOrderId()) {
            $purchase->setOrderId($order->getId())->save();
        }

        return $purchase;
    }

    /**
     * @return bool|\Mirasvit\Rewards\Model\Purchase
     */
    public function getPurchase()
    {
        $quote = $this->cartFactory->create()->getQuote();

        return $this->getByQuote($quote);
    }
}
