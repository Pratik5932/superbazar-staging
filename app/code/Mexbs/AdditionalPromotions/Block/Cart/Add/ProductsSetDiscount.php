<?php
namespace Mexbs\AdditionalPromotions\Block\Cart\Add;

use \Magento\Framework\View\Element\Template;
use Magento\Catalog\Block\Product\AbstractProduct;

class ProductsSetDiscount extends AbstractProduct
{
    private $helper;
    private $rule;
    private $quote;
    private $swatchesRenderer;
    private $ruleActionDetail;

    protected $_template = 'Mexbs_AdditionalPromotions::cart/promo/add/productsSetDiscount.phtml';

    public function __construct(
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\Quote\Model\Quote $quote,
        \Mexbs\AdditionalPromotions\Helper\Data $helper,
        \Magento\Swatches\Block\Product\Renderer\Listing\Configurable $swatchesRenderer,
        \Magento\Catalog\Block\Product\Context $context,
        array $data = []
    )
    {
        $this->helper = $helper;
        $this->rule = $rule;
        $this->quote = $quote;
        $this->swatchesRenderer = $swatchesRenderer;
        $this->swatchesRenderer->setTemplate('Mexbs_AdditionalPromotions::cart/promo/add/configurableProductRenderer.phtml');
        parent::__construct($context, $data);
    }
    public function getRuleProductGroups(){
        return $this->helper->getPromoProductGroupsForRule($this->rule, $this->quote);
    }
    public function getProductImageUrl($product){
        return $this->helper->getFullProductImageUrl($product->getImage());
    }
    public function getDetailsRenderer($type = null){
        if($type == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
            return $this->swatchesRenderer;
        }
        return parent::getDetailsRenderer($type);
    }

    public function getRuleActionDetail(){
        if(!$this->ruleActionDetail){
            $this->ruleActionDetail = $this->helper->getLoadedActionDetail($this->rule);
        }
        return $this->ruleActionDetail;
    }

    public function getRuleProductGroupsWithDisplayDataAndProducts(){
        return  $this->getRuleActionDetail()->getPromoProductGroupsWithDisplayData();
    }
    public function getAlreadyAddedMatchingRuleItemsWithDisplayData(){
        return  $this->getRuleActionDetail()->getAlreadyAddedMatchingRuleItemsWithDisplayData($this->quote);
    }
    public function getAlreadyAddedMatchingQtysPerGroup(){
        $alreadyAddedMatchingRuleItemsWithDisplayData = $this->getAlreadyAddedMatchingRuleItemsWithDisplayData();
        $alreadyAddedMatchingQtysPerGroup = [];
        foreach($alreadyAddedMatchingRuleItemsWithDisplayData as $groupNumber => $itemsData){
            foreach($itemsData as $itemData){
                if(!isset($alreadyAddedMatchingQtysPerGroup[$groupNumber])){
                    $alreadyAddedMatchingQtysPerGroup[$groupNumber] = 0;
                }
                $alreadyAddedMatchingQtysPerGroup[$groupNumber] += $itemData['qty'];
            }
        }
        return $alreadyAddedMatchingQtysPerGroup;
    }

    public function getTotalStepsNumber($productGroups, $alreadyMatchingQtysPerGroup){
        $totalStepsNumber = 0;
        foreach($productGroups as $groupNumber => $productGroup){
            if(!isset($alreadyMatchingQtysPerGroup[$groupNumber])
                || ($productGroup['qty'] > $alreadyMatchingQtysPerGroup[$groupNumber])){
                $totalStepsNumber++;
            }
        }
        return $totalStepsNumber;
    }
}