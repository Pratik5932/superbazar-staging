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

    class HyperLocalHelperPlugin  {

        protected $_request;

        public function __construct(
            \Magento\Framework\App\Request\Http $request
        )  {
            $this->_request            = $request;
        }
        public function afterGetSavedAddress(\Webkul\MpHyperLocal\Helper\Data $subject, $response)
        {
            $location              = [];
            $location["city"]      = $this->_request->getParam("city");
            $location["state"]     = $this->_request->getParam("state");
            $location["country"]   = $this->_request->getParam("country");
            $location["address"]   = $this->_request->getParam("address");
            $location["latitude"]  = $this->_request->getParam("latitude");
            $location["longitude"] = $this->_request->getParam("longitude"); 
            if ($location["longitude"] && $location["latitude"]) { 
                return $location;
            }  
            return $response;
        }
    }
