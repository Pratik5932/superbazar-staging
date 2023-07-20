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

namespace Webkul\MobikulMp\Plugin\Controller\Catalog;

use \Magento\Framework\Controller\ResultFactory;

/**
 * HomePageData class plugin for seller data
 */
class HomePageData
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
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * Constructor function for dependency management
     *
     * @param \Webkul\MobikulCore\Helper\Data $helper
     * @param \Webkul\Marketplace\Model\Seller $seller
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Webkul\Marketplace\Model\Seller $seller,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        ResultFactory $resultFactory
    ) {
        $this->helper        = $helper;
        $this->seller        = $seller;
        $this->customerFactory = $customerFactory;
        $this->resultFactory = $resultFactory;
    }

    /**
     * AfterExecute function
     *
     * @param \Webkul\MobikulApi\Controller\Catalog\HomePageData $subject
     * @param [type] $result
     * @return void
     */
    public function afterExecute(
        \Webkul\MobikulApi\Controller\Catalog\HomePageData $subject,
        $result
    ) {
        $request = $subject->getRequest()->getParams();
        $response = json_decode($result->getRawData());
        $customerToken = $request["customerToken"] ?? '';
        $storeId = $request["storeId"] ?? 1;

        $customerId  = $this->helper->getCustomerByToken($customerToken) ?? 0;
        $customer = $this->customerFactory->create()->load($customerId);

        if ($customer->getEmail() == $this->helper->getConfigData("mobikulmp/admin/email")) {
            $response->isAdmin = true;
        } else {
            $response->isAdmin = false;
        }
        $collection = $this->seller->getCollection()
            ->addFieldToFilter("seller_id", $customerId)
            ->addFieldToFilter("store_id", $storeId);
        // If seller data doesn't exist for current store //////////////////////////////
        if (!$collection->getSize()) {
            $collection = $this->seller->getCollection()
                ->addFieldToFilter("seller_id", $customerId)
                ->addFieldToFilter("store_id", 0);
        }
        foreach ($collection as $record) {
            $response->isSeller = true;
            if ($record->getIsSeller() == 0 || $record->getIsSeller() == 3) {
                $response->isPending = true;
            } else {
                $response->isPending = false;
            }

        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($response);
        return $resultJson;
    }
}
