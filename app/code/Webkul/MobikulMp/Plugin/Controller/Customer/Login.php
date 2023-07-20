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

namespace Webkul\MobikulMp\Plugin\Controller\Customer;

use \Magento\Framework\Controller\ResultFactory;

class Login
{

    /**
     * $helper
     */
    protected $helper;
    
    /**
     * $seller
     */
    protected $seller;
    
    /**
     * $request
     */
    protected $request;
    
    /**
     * $jsonHelper
     */
    protected $jsonHelper;
    
    /**
     * $resultFactory
     */
    protected $resultFactory;

    /**
     * Construct function for class Login
     *
     * @param \Webkul\MobikulCore\Helper\Data             $helper        helper
     * @param \Webkul\Marketplace\Model\Seller            $seller        seller
     * @param \Magento\Framework\App\Request\Http         $request       request
     * @param \Magento\Framework\Json\Helper\Data         $jsonHelper    jsonHelper
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory resultFactory
     *
     * @return void
     */
    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Webkul\Marketplace\Model\Seller $seller,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Controller\ResultFactory $resultFactory
    ) {
        $this->helper        = $helper;
        $this->seller        = $seller;
        $this->request       = $request;
        $this->jsonHelper    = $jsonHelper;
        $this->resultFactory = $resultFactory;
    }
 
    /**
     * Plugin afterExecute to add new parameters in the response
     *
     * @param \Webkul\MobikulApi\Controller\Customer\Login $subject  subject
     * @param object                                       $response response
     *
     * @return ResultFactory $this->returnArray
     */
    public function afterExecute(\Webkul\MobikulApi\Controller\Customer\Login $subject, $response)
    {
        if ($response->getRawData()) {
            $this->returnArray = $this->jsonHelper->jsonDecode($response->getRawData());
            if (isset($this->returnArray["success"]) && $this->returnArray["success"] == true) {
                $this->wholeData = $this->request->getPostValue();
                $this->storeId   = $this->helper->validate(
                    $this->wholeData,
                    "storeId"
                )  ? $this->wholeData["storeId"]  : 0;
                $this->username  = $this->helper->validate(
                    $this->wholeData,
                    "username"
                ) ? $this->wholeData["username"] : "";
                
                $this->customerId  = $this->helper->getCustomerByToken($this->returnArray["customerToken"]);
                if ($this->username == $this->helper->getConfigData("mobikulmp/admin/email")) {
                    $this->returnArray["isAdmin"] = true;
                } else {
                    $this->returnArray["isAdmin"] = false;
                }
                $collection = $this->seller->getCollection()
                    ->addFieldToFilter("seller_id", $this->customerId)
                    ->addFieldToFilter("store_id", $this->storeId);
                // If seller data doesn't exist for current store //////////////////////////////
                if (!count($collection)) {
                    $collection = $this->seller->getCollection()
                        ->addFieldToFilter("seller_id", $this->customerId)
                        ->addFieldToFilter("store_id", 0);
                }
                foreach ($collection as $record) {
                    $this->returnArray["isSeller"] = true;
                    if ($record->getIsSeller() == 0 || $record->getIsSeller() == 3) {
                        $this->returnArray["isPending"] = true;
                    } else {
                        $this->returnArray["isPending"] = false;
                    }

                }
                $resultJson  = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $resultJson->setData($this->returnArray);
                return $resultJson;
            }
        }
        return $response;
    }
}
