<?php

namespace Superbazaar\PostcodeWisePrice\Model\Source;

class Postcode implements \Magento\Framework\Option\ArrayInterface
{

    public function __construct(
        \Webkul\MpHyperLocal\Model\ShipAreaFactory $shipAreaFactory
    ) 
    {
        $this->shipAreaFactory = $shipAreaFactory;
    }

    public function toOptionArray()
    {
        $collection = $this->shipAreaFactory->create()->getCollection()->distinct(true)->addFieldToSelect('postcode')->setOrder('postcode', 'ASC');


        if(count($collection)){

            foreach($collection as $postCode){

                if($postCode->getPostcode()){

                    $array[] = [
                        'label' => $postCode->getPostcode(),
                        'value' => $postCode->getPostcode(),
                    ];
                }

            }
        }
        else{
            $array[] = [
                'label' => '',
                'value' => '',
            ];
        }

        return $array;

    }

}