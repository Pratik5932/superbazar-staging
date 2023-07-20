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


namespace Mirasvit\RewardsCatalog\Block\Product;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Message extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $context;
    /**
     * @var \Mirasvit\RewardsCatalog\Helper\Earn
     */
    private $earnHelper;
    /**
     * @var \Mirasvit\Rewards\Helper\Message
     */
    private $messageHelper;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Mirasvit\RewardsCatalog\Helper\EarnProductPage
     */
    private $earnProductPageHelper;

    public function __construct(
        \Mirasvit\Rewards\Helper\Message $messageHelper,
        \Mirasvit\RewardsCatalog\Helper\Earn $earnHelper,
        \Mirasvit\RewardsCatalog\Helper\EarnProductPage $earnProductPageHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->messageHelper = $messageHelper;
        $this->earnHelper    = $earnHelper;
        $this->earnProductPageHelper    = $earnProductPageHelper;
        $this->registry      = $registry;
        $this->context       = $context;
        $this->customerSession       = $customerSession;

        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->registry->registry('product') ?: $this->getCurrentProduct();
    }


    /**
     * @return string
     */
    public function getMessage()
    {
        $product = $this->getProduct();
        $product->setIsProductPage(true);
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $children = [];
            $products = $product->getTypeInstance()->getUsedProducts($product, null);
            foreach ($products as $childProduct) {
                $childProduct->setProduct($childProduct);
                $children[] = $childProduct;
            }
            $product->setChildren($children);
        }
        $this->earnHelper->getProductPoints(
            $product,
            $this->customerSession->getCustomer(),
            $this->context->getStoreManager()->getWebsite()->getId()
        ); //to collect messages
        $html = '';
        $messages = $this->earnProductPageHelper->getProductMessages($product->getId());
        foreach ($messages as $message) {
            $html .= $this->messageHelper->processNotificationVariables($message);
        }

        return $html;
    }
}
