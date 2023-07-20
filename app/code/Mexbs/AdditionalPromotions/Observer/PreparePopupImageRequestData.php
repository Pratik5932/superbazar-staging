<?php
namespace Mexbs\AdditionalPromotions\Observer;

use Magento\Framework\Event\ObserverInterface;

class PreparePopupImageRequestData implements ObserverInterface{

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();

        $data = $request->getPostValue();

        if(empty($data['popup_on_first_visit_image'])){
            unset($data['popup_on_first_visit_image']);
        }

        if(!isset($data['popup_on_first_visit_image'])){
            $data['popup_on_first_visit_image'] = [];
            $data['popup_on_first_visit_image']['delete'] = true;
        }

        if(isset($data['popup_on_first_visit_image'])
            && is_array($data['popup_on_first_visit_image'])){

            if(!empty($data['popup_on_first_visit_image']['delete'])){
                $data['popup_on_first_visit_image'] = '';
            }

            if(isset($data['popup_on_first_visit_image'][0]['name'])){
                $uploadedNewImage = false;
                if(isset($data['popup_on_first_visit_image'][0]['tmp_name'])){
                    $uploadedNewImage = true;
                }
                $data['popup_on_first_visit_image'] = $data['popup_on_first_visit_image'][0]['name'];
                if($uploadedNewImage){
                    $data['popup_on_first_visit_image_uploaded'] = true;
                }
            }
        }

        $request->setPostValue($data);
    }
}