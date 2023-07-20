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

class CreateAccount
{
    
    /**
     * $date
     */
    protected $date;
    
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
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date              date
     * @param \Webkul\Marketplace\Helper\Data             $marketplaceHelper marketplaceHelper
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory     resultFactory
     */
    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Webkul\Marketplace\Model\Seller $seller,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Framework\Controller\ResultFactory $resultFactory
    ) {
        $this->date              = $date;
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
     * @param \Webkul\MobikulApi\Controller\Customer\CreateAccount $subject subject
     * @param \Clsosure                                            $proceed proceed
     *
     * @return \Magento\Framework\Controller\ResultFactory
     */
    public function aroundExecute(\Webkul\MobikulApi\Controller\Customer\CreateAccount $subject, \Closure $proceed)
    {
        $resultJson  = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $this->returnArray  = [];
        $this->wholeData    = $this->request->getPostValue();
        $this->email        = $this->helper->validate($this->wholeData, "email") ? $this->wholeData["email"] : "";
        $this->shopUrl      = $this->helper->validate($this->wholeData, "shopUrl") ? $this->wholeData["shopUrl"] : "";
        $this->becomeSeller = $this->helper->validate(
            $this->wholeData,
            "becomeSeller"
        ) ? $this->wholeData["becomeSeller"] : 0;
        if ($this->becomeSeller == 1) {
            $model = $this->seller->getCollection()->addFieldToFilter("shop_url", $this->shopUrl);
            if (count($model)) {
                $this->returnArray["message"] = __("Shop URL already exist please set another.");
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $resultJson->setData($this->returnArray);
                return $resultJson;
            }
        }
        $response = $proceed();
        $this->returnArray = $this->jsonHelper->jsonDecode($response->getRawData());
        $this->returnArray["isAdmin"]   = false;
        $this->returnArray["isSeller"]  = false;
        $this->returnArray["isPending"] = false;
        if (!empty($this->returnArray["customerToken"])) {
            $this->customerId = $this->helper->getCustomerByToken($this->returnArray["customerToken"]);
            if ($this->becomeSeller == 1) {
                $status = $this->marketplaceHelper->getIsPartnerApproval() ? 0 : 1;
                $seller = $this->seller;
                $seller->setData("is_seller", $status);
                $seller->setData("shop_url", $this->shopUrl);
                $seller->setData("seller_id", $this->customerId);
                $seller->setCreatedAt($this->date->gmtDate());
                $seller->setUpdatedAt($this->date->gmtDate());
                $seller->setAdminNotification(1);
                $seller->save();
                $this->returnArray["isSeller"] = true;
                if ($status == 0) {
                    $this->returnArray["isPending"] = true;
                }
            } elseif ($this->getSeller($this->customerId)->getSellerId()) {
                $this->returnArray["isSeller"] = true;
                if ($this->getSeller($this->customerId)->getIsSeller() == 0) {
                    $this->returnArray["isPending"] = true;
                }
            }
        }
        if ($this->email == $this->helper->getConfigData("mobikulmp/admin/email")) {
            $this->returnArray["isAdmin"] = true;
        }
        $resultJson->setData($this->returnArray);
        return $resultJson;
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
