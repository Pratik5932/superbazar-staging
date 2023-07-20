<?php
namespace Superbazaar\CustomWork\Model\Config\Source;

class AttributeList
{
	protected $_customerGroup;
	
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeFactory
    ) {
        $this->_attributeFactory = $attributeFactory;
    }
	
    public function toOptionArray()
    {
		$attributes = [];
		$attributeCollection = $this->_attributeFactory->create();
		$removeFromList = [
			"category_gear",
			"category_ids",
			"created_at",
			"has_options",
			"image_label",
			"meta_title",
			"mp_product_cart_limit", 				
			"msrp_display_actual_price_type",
			"name",
			"news_from_date",
			"news_to_date",
			"page_layout",
			"quantity_and_stock_status",
			"required_options",
			"seller_id",
			"shipment_type",
			"sku",
			"small_image_label",
			"special_from_date",
			"special_to_date",
			"status",
			"supplier",
			"tax_class_id",
			"thumbnail_label",
			"tier_price",
			"updated_at",
			"url_path",
			"visibility",
			"weight2",
			"custom_design",
			"custom_design_from",
			"custom_design_to"
		];
		$allowedInputs = ["select", "multiselect", "text", "date"];
		foreach($attributeCollection as $attribute)
		{
			if (!in_array($attribute->getAttributeCode(), $removeFromList) && in_array($attribute->getFrontendInput(), $allowedInputs)) {
				$attributes[] = ['value' => $attribute->getAttributeCode(), 'label' => $attribute->getFrontendLabel()];
			}
			/*[attribute_code] => website_id
			[attribute_model] => 
			[backend_model] => Magento\Customer\Model\Customer\Attribute\Backend\Website
			[backend_type] => static
			[backend_table] => 
			[frontend_model] => 
			[frontend_input] => select
			[frontend_label] => Associate to Website*/
		}
		//echo "<pre>";
		//print_r($attributes);die;
		return $attributes;
    }
}
