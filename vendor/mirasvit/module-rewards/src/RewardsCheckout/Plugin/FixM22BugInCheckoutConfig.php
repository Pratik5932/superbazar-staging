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


namespace Mirasvit\RewardsCheckout\Plugin;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;

class FixM22BugInCheckoutConfig
{
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        CheckoutSession $checkoutSession
    ) {
        $this->productMetadata = $productMetadata;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array $args
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, ...$args)
    {
        if (version_compare($this->productMetadata->getVersion(), "2.2.2", ">=") &&
            version_compare($this->productMetadata->getVersion(), "2.3.0", "<")
        ) {
            // fix of magento bug https://github.com/magento/magento2/issues/12993
            // https://github.com/mirasvit/module-rewards/issues/183
            // PHP Fatal error:  Uncaught TypeError: Argument 1 passed to
            // Magento\Quote\Model\Cart\Totals::setExtensionAttributes() must be an instance of
            // Magento\Quote\Api\Data\TotalsExtensionInterface, instance of
            // Magento\Quote\Api\Data\AddressExtension given,
            $quote = $this->checkoutSession->getQuote();
            if ($quote->isVirtual()) {
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

        return $args;
    }
}
