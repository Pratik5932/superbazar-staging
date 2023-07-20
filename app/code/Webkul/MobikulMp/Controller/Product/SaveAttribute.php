<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulMp
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulMp\Controller\Product;

/**
 * Class SaveAttribute for saving product attribute from vendor's end
 *
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class SaveAttribute extends AbstractProduct
{

    /**
     * Execute function for class SaveAttribute
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment     = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->attributeOption = $this->jsonHelper->jsonDecode($this->attributeOption);
            $attributes     = $this->product->create()->getAttributes();
            $attributeCodes = [];
            foreach ($attributes as $attribute) {
                $attributeCodes[] = $attribute->getAttributeCode();
            }

            if (is_array(
                $attributeCodes
            ) && in_array(
                $this->attributeCode,
                $attributeCodes
            )
            ) {
                $this->returnArray["message"] = __("Attribute Code already exists");
            } else {
                $attributeOptionArray = [];
            }
            
            if (!empty($this->attributeOption)) {
                foreach ($this->attributeOption as $c) {
                    $attributeOptionArray[".".$c["admin"]."."] = [$c["admin"],$c["store"] ?? 0];
                }
            }
            if ($this->attributeCode == "") {
                $this->attributeCode = $this->generateAttrCode($this->attributeLabel);
            }
            if ($this->attributeCode) {
                $validatorRegx = new \Zend_Validate_Regex(["pattern"=>"/^[a-zA-Z0-9_]{0,30}$/"]);
                if (!$validatorRegx->isValid($this->attributeCode)) {
                    $this->returnArray["message"] = __(
                        "Attribute code '%1' is invalid. Please use only letters (a-z), numbers (0-9) or underscore(_) 
                        in this field, first character should be a letter.",
                        $this->attributeCode
                    );
                    $this->emulate->stopEnvironmentEmulation($environment);
                    $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
                    return $this->getJsonResponse($this->returnArray);
                }
            }
            $attributeData = [
                "apply_to"                      => 0,
                "is_global"                     => 1,
                "is_unique"                     => 0,
                "is_required"                   => $this->isRequired,
                "is_searchable"                 => 0,
                "is_comparable"                 => 0,
                "default_value"                 => "",
                "is_filterable"                 => 0,
                "attribute_code"                => $this->attributeCode,
                "frontend_input"                => $this->attributeType,
                "frontend_label"                => [$this->attributeLabel],
                "is_configurable"               => 1,
                "used_for_sort_by"              => 0,
                "default_value_text"            => "",
                "default_value_date"            => "",
                "is_wysiwyg_enabled"            => 0,
                "default_value_yesno"           => 0,
                "is_visible_on_front"           => 0,
                "default_value_textarea"        => "",
                "is_used_for_price_rules"       => 0,
                "used_in_product_listing"       => 0,
                "is_filterable_in_search"       => 0,
                "is_html_allowed_on_front"      => 1,
                "is_visible_in_advanced_search" => 1
            ];
            $model = $this->attributeModel;
            if (($model->getIsUserDefined() === null) || $model->getIsUserDefined() != 0) {
                $attributeData["backend_type"] = $model->getBackendTypeByInput($attributeData["frontend_input"]);
            }
            $model->addData($attributeData);
            $data["option"]["value"] = $attributeOptionArray;
            if ($this->attributeType == "select") {
                $model->addData($data);
            }
            $entityTypeID = $this->entityModel->setType("catalog_product")->getTypeId();
            $model->setEntityTypeId($entityTypeID);
            $model->setIsUserDefined(true);
            $model->save();
            $this->returnArray["message"] = __("Attribute Created Successfully");
            $this->returnArray["success"] = true;
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray, 1);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId         = $this->wholeData["storeId"]         ?? 0;
            $this->isRequired      = $this->wholeData["isRequired"]      ?? 0;
            $this->attributeCode   = $this->wholeData["attributeCode"]   ?? "";
            $this->attributeLabel  = $this->wholeData["attributeLabel"]  ?? "";
            $this->attributeType   = $this->wholeData["attributeType"]   ?? "";
            $this->attributeOption = $this->wholeData["attributeOption"] ?? "[]";
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }

    /**
     * Function to generate Attribute code from attribute label
     *
     * @param string $attributeLabel attribute Label
     *
     * @return string attribute code
     */
    protected function generateAttrCode($attributeLabel)
    {
        $attributeLabelFormatUrlKey = $this->productUrl->formatUrlKey($attributeLabel);
        $attributeCode = substr(preg_replace("/[^a-z_0-9]/", "_", $attributeLabelFormatUrlKey), 0, 30);
        $validatorAttrCode = new \Zend_Validate_Regex(["pattern"=>"/^[a-z][a-z_0-9]{0,29}[a-z0-9]$/"]);
        if (!$validatorAttrCode->isValid($attributeCode)) {
            $attributeCode = "attr_".($attributeCode ?: substr(hash("sha256", time()), 0, 8));
        }
        return $attributeCode;
    }
}
