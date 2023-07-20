<?php
namespace Mexbs\AdditionalPromotions\Model\Plugin\Rule;

class DataProvider
{
    private $apHelper;

    public function __construct(
        \Mexbs\AdditionalPromotions\Helper\Data $apHelper
    ){
        $this->apHelper = $apHelper;
    }

    public function afterGetData(
        \Magento\SalesRule\Model\Rule\DataProvider $subject,
        $data
    ){
        if(!is_array($data)){
            return $data;
        }
        foreach($data as $ruleId => $ruleData){
            if (isset($ruleData['popup_on_first_visit_image'])
                && !empty($ruleData['popup_on_first_visit_image'])) {
                $imageName = $ruleData['popup_on_first_visit_image'];
                $data[$ruleId]['popup_on_first_visit_image'] = [];
                $data[$ruleId]['popup_on_first_visit_image'][0] = [];
                $data[$ruleId]['popup_on_first_visit_image'][0]['name'] = $imageName;
                $data[$ruleId]['popup_on_first_visit_image'][0]['url'] = $this->apHelper->getSalesRulePopupImageUrl($imageName);
            }
        }
        return $data;
    }
}