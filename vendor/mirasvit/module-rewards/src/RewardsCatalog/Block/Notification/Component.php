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



namespace Mirasvit\RewardsCatalog\Block\Notification;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Adds js notification component on the page
 *
 * @package Mirasvit\RewardsCatalog\Block\Notification
 */
class Component extends Template
{
    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Registry $registry,
        Context $context,
        array $data = []
    ) {
        $this->registry = $registry;

        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->registry->registry('product');
    }

    /**
     * @return string
     */
    public function getNotificationUrl()
    {
        return $this->getUrl(
            'rewards_catalog/notification/getProductNotification', ['product_id' => $this->getProduct()->getId()]
        );
    }
}
