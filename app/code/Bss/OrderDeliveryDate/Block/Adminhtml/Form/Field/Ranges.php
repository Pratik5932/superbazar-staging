<?php
/**
* BSS Commerce Co.
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://bsscommerce.com/Bss-Commerce-License.txt
*
* @category   BSS
* @package    Bss_OrderDeliveryDate
* @author     Extension Team
* @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/
namespace Bss\OrderDeliveryDate\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class Ranges extends AbstractFieldArray
{

    private $taxRenderer;

    protected function _prepareToRender()
    {
        $this->addColumn('price', ['label' => __('Postcode')]);

        $this->addColumn('deliverydate_day_off', [
            'label' => __('Disable Delivery Date'),
            'renderer' => $this->getCountryRenderer(),
            'extra_params' => 'multiple="multiple"'
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    protected function _prepareArrayRow(DataObject $row)
    {
        $options = [];


        $tax = $row->getDeliverydateDayOff();

        if ($tax !== null) {
            foreach ($tax as $country) {
                #echo $country;exit;
                $options['option_' . $this->getCountryRenderer()->calcOptionHash($country)]

                = 'selected="selected"';
            }
        }
        #s print_r($options);exit;
        $row->setData('option_extra_attrs', $options);
    }


    private function getCountryRenderer()
    {
        $this->countryRenderer = $this->getLayout()->createBlock(
            \Bss\OrderDeliveryDate\Block\Adminhtml\Form\Field\CountryColumn::class,
            '',
            ['data' => ['is_render_to_js_template' => true]]
        );
        return $this->countryRenderer;
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // $html = $element->getElementHtml();
        #echo $html;
        $value = $element->getData('value');

        #print_r($value);exit;
        $dayoffArray = [];
        /* if(is_array($value) && count($value)>0){
        foreach($value as $key =>$dateArray){
        #echo $key;exit;
        $dayoffArray[$key]=$dateArray['deliverydate_day_off'];
        # print_R($dateArray);


        }
        }*/
        $script="";
        #print_R($value);exit;
        if(is_array($value) && count($value)>0){
            foreach($value as $key1=> $dateArray1){
                if(isset($dateArray1['deliverydate_day_off'])){
                    # print_R($dateArray1);exit;
                    //if($key ==  $element->getHtmlId()){
                    # echo $key;exit;
                    $arr= implode(",",$dateArray1['deliverydate_day_off']);
                    // $key1 = $key1;
                    #echo $arr;exit;
                    #echo $key1;exit;
                    $script = " <script>
                    require([
                    'jquery',
                    ], function ($) {
                    $(document).ready(function() {
                    setTimeout(function () {




                    $('#orderdeliverydate_general_deliverydate_day_off tbody  tr').each(function(index, tr) { 
                    console.log(tr);
                    var test = $(this).find('select').attr('name').replace(/[\[\]']/g,',' );
                    var datearray = test.split(',');
                    var key = '$key1';
                    console.log(key);
                    console.log(datearray.includes(key));
                    if(datearray.includes(key) == true){
                    console.log('asdasd');
                    $(tr).find('select').val([$arr]);

                    }

                    });
                    }, 2500);

                    })
                    })
                    </script>";   
                    //    }
                    //}
                }
            }
            # print_R($dayoffArray);exit;

            #$dayoffArray = implode(",",$dayoffArray);


        }
        return parent::_getElementHtml($element) . $script;
    }
}