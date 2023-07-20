<?php
    /**
     * Webkul Software.
     *
     * @category  Webkul
     * @package   Webkul_MobikulMpHyperLocal
     * @author    Webkul
     * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
     * @license   https://store.webkul.com/license.html
     */

    namespace Webkul\MobikulMpHyperLocal\Plugin\Helper;

    use \Magento\Framework\Controller\ResultFactory;
    use Magento\Framework\Pricing\Helper\Data as PriceHelper;
    use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

    class Catalog  {

        protected $_request;
        protected $_category;
        protected $_hyperLocalHelper;


        public function __construct(
            \Webkul\MpHyperLocal\Helper\Data $hyperLocalHelper,
            \Magento\Framework\App\Request\Http $request,
            \Magento\Catalog\Model\Category $category
        )  {
            $this->_request            = $request;
            $this->_category           = $category;
            $this->_hyperLocalHelper   = $hyperLocalHelper;
        }
        public function afterGetProductListColl(\Webkul\MobikulCore\Helper\Catalog $subject, $response)
        {   
            $latitude  = $this->_request->getParam("latitude");
            $longitude = $this->_request->getParam("longitude");

            if ($latitude && $longitude) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $sellerIds = $this->_hyperLocalHelper->getNearestSellers();
                $allowedProList = $this->_hyperLocalHelper->getNearestProducts($sellerIds);
                return $response->addAttributeToFilter('entity_id', ['in' => $allowedProList]);
            }
            return $response;
        }
    }