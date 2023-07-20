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


namespace Mirasvit\RewardsCheckout\Model\Cart;

use \Magento\Framework\Api\ExtensibleDataInterface;

class CartRepositoryPlugin
{
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->productMetadata = $productMetadata;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Quote\Model\QuoteRepository $subject
     * @param \Magento\Quote\Api\Data\CartInterface      $quote
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(\Magento\Quote\Model\QuoteRepository $subject, $quote)
    {
        $this->fixAddress($quote);

        return $quote;
    }

    /**
     * @param \Magento\Quote\Model\QuoteRepository $subject
     * @param \Magento\Quote\Api\Data\CartInterface      $quote
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetActive(\Magento\Quote\Model\QuoteRepository $subject, $quote)
    {
        $this->fixAddress($quote);

        return $quote;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return void
     */
    protected function fixAddress($quote)
    {
        if ($quote && version_compare($this->productMetadata->getVersion(), "2.2.2", ">=") &&
            version_compare($this->productMetadata->getVersion(), "2.3.0", "<")
        ) {
            // fix of magento bug https://github.com/magento/magento2/issues/12993
            // https://github.com/mirasvit/module-rewards/issues/183
            // PHP Fatal error:  Uncaught TypeError: Argument 1 passed to
            // Magento\Quote\Model\Cart\Totals::setExtensionAttributes() must be an instance of
            // Magento\Quote\Api\Data\TotalsExtensionInterface, instance of
            // Magento\Quote\Api\Data\AddressExtension given

            // We do not use $quote->isVirtual() because for some reason Amasty Shipping Rate takes wrong cart id
            if ($quote->getData('is_virtual')) {
                $addressTotalsData = $quote->getBillingAddress()->getData();
                if (isset($addressTotalsData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY])) {
                    unset($addressTotalsData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);
                    $quote->getBillingAddress()->setData($addressTotalsData)->save();
                }
            } else {
                $addressTotalsData = $quote->getShippingAddress()->getData();
                if (isset($addressTotalsData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY])) {
                    unset($addressTotalsData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);
                    $quote->getShippingAddress()->setData($addressTotalsData)->save();
                }
            }
        }
    }
}