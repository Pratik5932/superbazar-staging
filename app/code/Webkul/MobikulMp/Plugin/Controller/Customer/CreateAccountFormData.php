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

class CreateAccountFormData
{
    /**
     * $helper
     */
    protected $helper;
    
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
     * @param \Magento\Framework\App\Request\Http         $request       request
     * @param \Magento\Framework\Json\Helper\Data         $jsonHelper    jsonHelper
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory resultFactory
     *
     * @return void
     */
    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Controller\ResultFactory $resultFactory
    ) {
        $this->helper        = $helper;
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
    public function afterExecute(\Webkul\MobikulApi\Controller\Customer\CreateAccountFormData $subject, $response)
    {
        if ($response->getRawData()) {
            $this->returnArray = $this->jsonHelper->jsonDecode($response->getRawData());
            $this->returnArray["sellerRegistrationStatus"] =
                (bool) $this->helper->getConfigData("marketplace/landingpage_settings/allow_seller_registration_block");
            $resultJson  = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($this->returnArray);
            return $resultJson;
        }
        return $response;
    }
}
