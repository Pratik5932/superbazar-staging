<?php
namespace Mexbs\AdditionalPromotions\Model\Plugin;

class SalesRule
{
    protected $apHelper;
    protected $serializer;

    public function __construct(
        \Mexbs\AdditionalPromotions\Helper\Data $apHelper,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer
    ) {
        $this->apHelper = $apHelper;
        $this->serializer = $serializer;
    }

    protected function _convertFlatToRecursive(array $data)
    {
        $arr = [];
        foreach ($data as $key => $value) {
            if (($key === 'action_details') && is_array($value)) {
                foreach($data[$key]['1--1'] as $mainDataKey => $mainDataValue){
                    $arr[$mainDataKey] = $mainDataValue;
                }

                foreach($data[$key] as $subTypeKey => $subTypeValue){
                    if($subTypeKey != '1--1'){
                        $arr[$subTypeKey] = [];

                        foreach($subTypeValue as $subTypeChildrenDataKey => $subTypeChildrenDataValue){
                            $subTypeChildrenDataPath = explode('--', $subTypeChildrenDataKey);

                            $workingArr = & $arr[$subTypeKey];
                            $workingArrDepth = 0;
                            foreach($subTypeChildrenDataPath as $subTypeChildrenDataPathIndex){
                                $workingArrDepth++;

                                $flatArrayKey = implode("--", array_slice($subTypeChildrenDataPath, 0, $workingArrDepth));

                                if(!isset($workingArr['action_details'][$subTypeChildrenDataPathIndex])){
                                    if(isset($data[$key][$subTypeKey][$flatArrayKey])){
                                        $workingArr['action_details'][$subTypeChildrenDataPathIndex] = $data[$key][$subTypeKey][$flatArrayKey];
                                    }else{
                                        $workingArr['action_details'][$subTypeChildrenDataPathIndex] = [];
                                    }
                                }

                                $workingArr = & $workingArr['action_details'][$subTypeChildrenDataPathIndex];
                            }
                        }
                    }
                }
            }
        }

        return $arr;
    }

    protected function _loadApActionDetails($data, $rule){
        $simpleAction = (isset($data['simple_action']) ? $data['simple_action'] : null);
        if(!$simpleAction){
            return $data;
        }

        if(!$this->apHelper->isSimpleActionAp($simpleAction)){
            return $data;
        }

        $type = $this->apHelper->getSimpleActionType($simpleAction);


        $arr = $this->_convertFlatToRecursive($data);

        if($type){
            if(isset($arr['type'])){
                if ($rule->hasActionDetailsSerialized()) {
                    $rule->unsActionDetailsSerialized();
                }

                $actionDetailLoaded = $this->apHelper->getLoadedActionDetailByArrayAndType($type, $rule, $arr);
            }else{
                if($rule->hasActionDetailsSerialized()){
                    $actionDetailsUnserialized = $this->serializer->unserialize($rule->getActionDetailsSerialized());
                    $rule->unsActionDetailsSerialized();

                    $actionDetailLoaded = $this->apHelper->getLoadedActionDetailByArrayAndType($type, $rule, $actionDetailsUnserialized);
                }
            }

            if(isset($actionDetailLoaded)){
                $actionDetailAsArray = $this->apHelper->getLoadedActionDetailAsArray($actionDetailLoaded);
                $data['action_details_serialized'] = $this->serializer->serialize($actionDetailAsArray);
            }
        }

        return $data;
    }

    public function beforeLoadPost(
        \Magento\SalesRule\Model\Rule $rule,
        array $data
    ){
        $data = $this->_loadApActionDetails($data, $rule);

        return [$data];
    }
}