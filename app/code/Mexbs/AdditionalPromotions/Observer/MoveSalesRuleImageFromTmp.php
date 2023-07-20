<?php
namespace Mexbs\AdditionalPromotions\Observer;

use Magento\Framework\Event\ObserverInterface;

class MoveSalesRuleImageFromTmp implements ObserverInterface{
    protected $imageUploader;

    public function __construct(
        \Mexbs\AdditionalPromotions\Model\ImageUploader $imageUploader
    ) {
        $this->imageUploader = $imageUploader;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $rule = $observer->getEntity();
        if(!empty($rule->getPopupOnFirstVisitImage())
            && $rule->getPopupOnFirstVisitImageUploaded()){
            try{
                $this->imageUploader->moveFileFromTmpDirectory($rule->getPopupOnFirstVisitImage());
            }catch(\Exception $e){}
        }
    }
}