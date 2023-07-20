<?php
namespace Mexbs\AdditionalPromotions\Block\Widget;

use \Magento\Framework\View\Element\Template;

class PromosList extends \Mexbs\AdditionalPromotions\Block\PromoProducts implements \Magento\Widget\Block\BlockInterface
{
    public function __construct(
        \Mexbs\AdditionalPromotions\Helper\Data $helper,
        Template\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        array $data = []
    )
    {
        parent::__construct(
            $helper,
            $context,
            $cart,
            $data
        );
        $this->setTemplate('Mexbs_AdditionalPromotions::widget/inject-promo-products.phtml');
    }
}