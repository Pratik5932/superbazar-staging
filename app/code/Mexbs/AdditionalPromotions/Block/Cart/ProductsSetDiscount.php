<?php
namespace Mexbs\AdditionalPromotions\Block\Cart;

use \Magento\Framework\View\Element\Template;
class ProductsSetDiscount extends Template
{
    protected $_template = 'Mexbs_AdditionalPromotions::cart/promo/productsSetDiscount.phtml';

    private $helper;
    private $rule;
    private $quote;
    private $ruleActionDetail;

    public function __construct(
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\Quote\Model\Quote $quote,
        \Mexbs\AdditionalPromotions\Helper\Data $helper,
        Template\Context $context,
        array $data = []
    )
    {
        $this->helper = $helper;
        $this->rule = $rule;
        $this->quote = $quote;
        parent::__construct($context, $data);
    }

    public function getRuleName(){
        return $this->rule->getName();
    }

    public function getRuleId(){
        return $this->rule->getId();
    }

    public function getIsSomeProductsHasOptions(){
        return  $this->getRuleActionDetail()->getIsSomeProductsHasOptions();
    }

    public function getIsRuleHasSelections(){
        return  $this->getRuleActionDetail()->getIsRuleHasSelections();
    }

    public function getRuleProductGroupsWithDisplayDataAndProducts(){
        return  $this->getRuleActionDetail()->getPromoProductGroupsWithDisplayData();
    }

    public function getAlreadyAddedMatchingRuleItemsWithDisplayData(){
        return  $this->getRuleActionDetail()->getAlreadyAddedMatchingRuleItemsWithDisplayData($this->quote);
    }

    public function getProductGroupImages($productGroup){
        $productGroupImages = [];
        foreach($productGroup['products'] as $product){
            $productGroupImages[] = $product['image'];
        }
        return $productGroupImages;
    }

    public function getRuleActionDetail(){
        if(!$this->ruleActionDetail){
            $this->ruleActionDetail = $this->helper->getLoadedActionDetail($this->rule);
        }
        return $this->ruleActionDetail;
    }

    public function getRuleDiscountDescription(){
        return $this->getRuleActionDetail()->getRuleDiscountDescription();
    }
}