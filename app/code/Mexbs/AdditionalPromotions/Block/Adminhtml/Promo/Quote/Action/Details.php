<?php
namespace Mexbs\AdditionalPromotions\Block\Adminhtml\Promo\Quote\Action;

class Details extends \Magento\Backend\Block\Template
{
    const ACTIONS_SECTION_NAME = 'actions_apply_to';

    protected $coreRegistry;
    protected $apHelper;
    protected $loadedActionDetail;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Mexbs\AdditionalPromotions\Helper\Data $apHelper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->setTemplate("Mexbs_AdditionalPromotions::promo/action/details.phtml");
        $this->coreRegistry = $registry;
        $this->apHelper = $apHelper;
    }

    public function getMagentoVersion(){
        return $this->apHelper->getMagentoVersion();
    }

    public function getAllApSimpleActions(){
        return array_keys($this->apHelper->apSimpleActionsToTypes);
    }

    public function getNewChildUrl(){
        $actionFieldSetId = "sales_rule_formrule_action_details_fieldset_";
        $formName = 'sales_rule_form';

        return $this->getUrl(
            'additional_promotions/promo_quote/newActionDetailsHtml/form/' . $actionFieldSetId,
            ['form_namespace' => $formName]
        );
    }

    public function getActionDetailLoaded(){
        if($this->loadedActionDetail){
            return $this->loadedActionDetail;
        }

        $rule = $this->coreRegistry->registry(\Magento\SalesRule\Model\RegistryConstants::CURRENT_SALES_RULE);
        if($rule){
            $this->loadedActionDetail = $this->apHelper->getLoadedActionDetail($rule);
        }

        return $this->loadedActionDetail;
    }

    public function getChildrenHtml(){
        $actionDetailLoaded = $this->getActionDetailLoaded();

        if(!$actionDetailLoaded){
            return '';
        }
        return $actionDetailLoaded->asHtmlRecursive();
    }

    public function getAllApFieldNames(){
        return [
            'discount_order_type',
            'max_groups_number',
            'max_sets_number',
            'max_discount_amount',
            'skip_special_price',
            'skip_tier_price'
        ];
    }

    public function getAllNonApFieldNames(){
        return [
            'discount_amount',
            'discount_qty',
            'discount_step',
            'apply_to_shipping',
            'stop_rules_processing',
            'actions_apply_to'
        ];
    }

    public function getAllFieldNames(){
        return array_merge($this->getAllNonApFieldNames(), $this->getAllApFieldNames());
    }

    public function getApSimpleActionFieldNamesShowSetting(){
        return
        [
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetYForEachXSpent::SIMPLE_ACTION =>
            [
                'stop_rules_processing',
                'discount_order_type',
                'skip_special_price',
                'skip_tier_price',
                'max_discount_amount'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetYForEachXSpentUpToN::SIMPLE_ACTION =>
            [
                'stop_rules_processing',
                'skip_special_price',
                'skip_tier_price',
                'max_discount_amount'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyXGetNOfYPercentDiscount::SIMPLE_ACTION =>
            [
                'stop_rules_processing',
                'discount_qty',
                'skip_special_price',
                'skip_tier_price',
                'max_discount_amount'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyXGetNOfYFixedDiscount::SIMPLE_ACTION =>
            [
                'stop_rules_processing',
                'discount_qty',
                'skip_special_price',
                'skip_tier_price',
                'max_discount_amount'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyXGetNOfYFixedPriceDiscount::SIMPLE_ACTION =>
            [
                'stop_rules_processing',
                'discount_qty',
                'skip_special_price',
                'skip_tier_price',
                'max_discount_amount'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyABCGetNOfDPercentDiscount::SIMPLE_ACTION =>
            [
                'stop_rules_processing',
                'skip_special_price',
                'skip_tier_price',
                'max_discount_amount'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyABCGetNOfDFixedDiscount::SIMPLE_ACTION =>
            [
                'stop_rules_processing',
                'skip_special_price',
                'skip_tier_price',
                'max_discount_amount'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\BuyABCGetNOfDFixedPriceDiscount::SIMPLE_ACTION =>
            [
                'stop_rules_processing',
                'skip_special_price',
                'skip_tier_price',
                'max_discount_amount'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetEachNAfterMPercentDiscount::SIMPLE_ACTION =>
            [
                'discount_qty',
                'stop_rules_processing',
                'skip_special_price',
                'skip_tier_price',
                'max_discount_amount'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetEachNAfterMFixedDiscount::SIMPLE_ACTION =>
            [
                'discount_qty',
                'stop_rules_processing',
                'skip_special_price',
                'skip_tier_price',
                'max_discount_amount'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetEachNAfterMFixedPriceDiscount::SIMPLE_ACTION =>
            [
                'discount_qty',
                'stop_rules_processing',
                'skip_special_price',
                'skip_tier_price',
                'max_discount_amount'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetAllAfterMPercentDiscount::SIMPLE_ACTION =>
            [
                'stop_rules_processing',
                'discount_order_type',
                'discount_qty',
                'skip_special_price',
                'skip_tier_price',
                'max_discount_amount'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetAllAfterMFixedDiscount::SIMPLE_ACTION =>
            [
                'stop_rules_processing',
                'discount_order_type',
                'discount_qty',
                'skip_special_price',
                'skip_tier_price',
                'max_discount_amount'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\GetAllAfterMFixedPriceDiscount::SIMPLE_ACTION =>
            [
                'stop_rules_processing',
                'discount_order_type',
                'discount_qty',
                'skip_special_price',
                'skip_tier_price',
                'max_discount_amount'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\EachGroupOfNPercentDiscount::SIMPLE_ACTION =>
            [
                'stop_rules_processing',
                'discount_order_type',
                'max_groups_number',
                'skip_special_price',
                'skip_tier_price',
                'max_discount_amount'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\EachGroupOfNFixedDiscount::SIMPLE_ACTION =>
            [
                'stop_rules_processing',
                'discount_order_type',
                'max_groups_number',
                'skip_special_price',
                'skip_tier_price',
                'max_discount_amount'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\EachGroupOfNFixedPriceDiscount::SIMPLE_ACTION =>
            [
                'stop_rules_processing',
                'discount_order_type',
                'max_groups_number',
                'skip_special_price',
                'skip_tier_price',
                'max_discount_amount'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\ProductsSetPercentDiscount::SIMPLE_ACTION =>
            [
                'stop_rules_processing',
                'discount_order_type',
                'max_sets_number',
                'skip_special_price',
                'skip_tier_price',
                'max_discount_amount'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\ProductsSetFixedDiscount::SIMPLE_ACTION =>
            [
                'stop_rules_processing',
                'discount_order_type',
                'max_sets_number',
                'skip_special_price',
                'skip_tier_price',
                'max_discount_amount'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\ProductsSetFixedPriceDiscount::SIMPLE_ACTION =>
            [
                'stop_rules_processing',
                'discount_order_type',
                'max_sets_number',
                'skip_special_price',
                'skip_tier_price',
                'max_discount_amount'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\FirstNNextMAfterKPercentDiscount::SIMPLE_ACTION =>
            [
                'max_discount_amount',
                'discount_order_type',
                'stop_rules_processing',
                'skip_special_price',
                'skip_tier_price'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\FirstNNextMAfterKFixedDiscount::SIMPLE_ACTION =>
            [
                'max_discount_amount',
                'discount_order_type',
                'stop_rules_processing',
                'skip_special_price',
                'skip_tier_price'
            ],
            \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\FirstNNextMAfterKFixedPriceDiscount::SIMPLE_ACTION =>
            [
                'max_discount_amount',
                'discount_order_type',
                'stop_rules_processing',
                'skip_special_price',
                'skip_tier_price'
            ]
        ];
    }
}