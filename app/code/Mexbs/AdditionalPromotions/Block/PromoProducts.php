<?php
namespace Mexbs\AdditionalPromotions\Block;

use \Magento\Framework\View\Element\Template;

class PromoProducts extends \Magento\Framework\View\Element\Template
{
    private $helper;
    protected $cart;

    public function __construct(
        \Mexbs\AdditionalPromotions\Helper\Data $helper,
        Template\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        array $data = []
    )
    {
        $this->helper = $helper;
        $this->cart = $cart;
        parent::__construct($context, $data);
    }

    public function isCacheable(){
        return false;
    }

    public function getPromoBlockTitle(){
        return $this->helper->getConfigPromoBlockTitle();
    }

    public function getPromoBlockRulesHtml(){
        $promoBlockRulesHtmlArray = $this->helper->getPromoBlockRulesHtmlArray($this->cart->getQuote());
        $promoBlockRulesHtml = '';
        foreach($promoBlockRulesHtmlArray as $html){
            $promoBlockRulesHtml .= $html;
        }
        return $promoBlockRulesHtml;
    }
}