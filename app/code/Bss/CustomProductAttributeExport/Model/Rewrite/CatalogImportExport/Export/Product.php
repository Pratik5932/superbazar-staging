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
* =================================================================
*                 MAGENTO EDITION USAGE NOTICE
* =================================================================
* This package designed for Magento COMMUNITY edition
* BSS Commerce does not guarantee correct work of this extension
* on any other Magento edition except Magento COMMUNITY edition.
* BSS Commerce does not provide extension support in case of
* incorrect edition usage.
* =================================================================
*
* @category   BSS
* @package    Bss_CustomProductAttributeExport
* @author     Extension Team
* @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/
namespace Bss\CustomProductAttributeExport\Model\Rewrite\CatalogImportExport\Export;

class Product extends \Magento\CatalogImportExport\Model\Export\Product
{
    protected function setHeaderColumns($customOptionsData, $stockItemRows)
    {
        $config = \Magento\Framework\App\ObjectManager::getInstance()
        ->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $moduleEnabled = (bool)$config->getValue('customproductattributeexport/configuration/enable');
        $merge = [];
        if ($moduleEnabled) {
            $attr = $config->getValue('customproductattributeexport/configuration/allowedattribute');
            $merge = explode(',', $attr);
        }

        if (!$this->_headerColumns) {
            $customOptCols = [
                'custom_options',
            ];
            $this->_headerColumns = array_merge(
                [
                    self::COL_SKU,
                    self::COL_STORE,
                    self::COL_ATTR_SET,
                    self::COL_TYPE,
                    self::COL_CATEGORY,
                    self::COL_PRODUCT_WEBSITES,
                ],
                $merge,
                reset($stockItemRows) ? array_keys(end($stockItemRows)) : []
            );
        }
    }
}
