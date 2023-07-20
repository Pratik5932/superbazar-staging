<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpHyperLocal\Plugin\Catalog;

use \Webkul\MpHyperLocal\Helper\Data;

class ListProduct
{
    /**
     * @var \Webkul\MpHyperLocal\Helper\Data
     */
    protected $helper;
    
    /**
     * @param \Webkul\MpHyperLocal\Helper\Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    public function aroundGetLoadedProductCollection(\Magento\Catalog\Block\Product\ListProduct $subject, \Closure $proceed)
    {   
	#echo "adas";exit;
        $result = $proceed();
        $savedAddress = $this->helper->getSavedAddress();
        if ($savedAddress) {
            $sellerIds = $this->helper->getNearestSellers();
            $allowedProList = $this->helper->getNearestProducts($sellerIds);
            $result->addAttributeToFilter('entity_id', ['in' => $allowedProList]);
        }
		#echo count($result);exit;
        return $result;
    }
}
