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

class SaveReview
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
     * $marketplaceHelper
     */
    protected $marketplaceHelper;

    /**
     * Construct function for plugin class Create Account
     *
     * @param \Webkul\MobikulCore\Helper\Data             $helper            helper
     * @param \Webkul\Marketplace\Model\Seller            $seller            seller
     * @param \Magento\Framework\App\Request\Http         $request           request
     * @param \Magento\Framework\Json\Helper\Data         $jsonHelper        jsonHelper
     * @param \Webkul\Marketplace\Helper\Data             $marketplaceHelper marketplaceHelper
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory     resultFactory
     */
    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Webkul\Marketplace\Model\Seller $seller,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Framework\Controller\ResultFactory $resultFactory
    ) {
        $this->helper            = $helper;
        $this->seller            = $seller;
        $this->request           = $request;
        $this->jsonHelper        = $jsonHelper;
        $this->resultFactory     = $resultFactory;
        $this->marketplaceHelper = $marketplaceHelper;
    }

    /**
     * Plugin function aroundExecute
     *
     * @param \Webkul\MobikulApi\Controller\Customer\SaveReview $subject subject
     * @param \Clsosure                                            $proceed proceed
     *
     * @return \Magento\Framework\Controller\ResultFactory
     */
    public function aroundExecute(\Webkul\MobikulApi\Controller\Customer\SaveReview $subject, \Closure $proceed)
    {
        $resultJson  = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $this->returnArray  = [];
        $this->wholeData    = $this->request->getPostValue();
        $this->storeId = $this->wholeData['storeId'] ?? 0;
        $this->productId = $this->wholeData['productId'] ?? 0;
        $this->customerToken = $this->wholeData['customerToken'] ?? '';
        $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;

        if ($this->customerId) {
            if ($this->getSeller($this->customerId)->getSellerId()) {
                $productData = $this->marketplaceHelper->getSellerProductDataByProductId($this->productId);
                if ($productData->getSize() &&
                    $productData->getFirstItem()->getSellerId() == $this->getSeller($this->customerId)->getSellerId()
                ) {
                    $this->returnArray['status'] = false;
                    $this->returnArray['message'] = __('You can not review your own product.');
                    $resultJson->setData($this->returnArray);
                    return $resultJson;
                }
      
            }
        }

        return $proceed();
    }

    /**
     * Function getSeller To get Seller Data
     *
     * @param integer $customerId customer Id
     *
     * @return \Webkul\Marketplace\Model\Seller
     */
    public function getSeller($customerId)
    {
        return $this->seller->getCollection()
            ->addFieldToFilter("seller_id", $customerId)
            ->setPagesize(1)
            ->getFirstItem();
    }
}
