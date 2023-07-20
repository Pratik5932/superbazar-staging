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

class ProductPageData
{
    /**
     * Instance of \Webkul\MobikulCore\Helper\Data
     *
     * @var \Webkul\Mobikul\Helper\Data
     */
    protected $helper;
    
    /**
     * Instance of \Magento\Framework\App\Request\Http
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    
    /**
     * Instance of \Magento\Framework\Json\Helper\Data
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    
    /**
     * Instance of \Magento\Framework\View\Element\Template
     *
     * @var \Magento\Framework\View\Element\Template
     */
    protected $viewTemplate;
    
    /**
     * Instance of ResultFactory
     *
     * @var ResultFactory
     */
    protected $resultFactory;
    
    /**
     * Instance of \Webkul\Marketplace\Helper\Data
     *
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * Construct Function for plugin class ProductPageData
     *
     * @param ResultFactory                            $resultFactory     resultFactory
     * @param \Webkul\MobikulCore\Helper\Data          $helper            helper
     * @param \Magento\Framework\App\Request\Http      $request           request
     * @param \Magento\Framework\Json\Helper\Data      $jsonHelper        jsonHelper
     * @param \Webkul\Marketplace\Helper\Data          $marketplaceHelper marketplaceHelper
     * @param \Magento\Framework\View\Element\Template $viewTemplate      viewTemplate
     *
     * @return void
     */
    public function __construct(
        ResultFactory $resultFactory,
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Framework\View\Element\Template $viewTemplate
        // \Webkul\Marketplace\Model\ProductFlagReason $productFlagReason
    ) {
        $this->helper            = $helper;
        $this->request           = $request;
        $this->jsonHelper        = $jsonHelper;
        $this->viewTemplate      = $viewTemplate;
        $this->resultFactory     = $resultFactory;
        $this->marketplaceHelper = $marketplaceHelper;
        // $this->productFlagReason = $productFlagReason;
    }

    /**
     * Plugin afterExecute to add new parameters in the response
     *
     * @param \Webkul\Mobikul\Controller\Catalog\ProductPageData $subject  subject
     * @param obj                                                $response response
     *
     * @return ResultFactory $returnarray
     */
    public function afterExecute(\Webkul\MobikulApi\Controller\Catalog\ProductPageData $subject, $response)
    {
        $returnArray = json_decode($response->getRawData());
        $wholeData   = $this->request->getParams();
        $authKey     = $this->request->getHeader("authKey");
        $authData    = $this->helper->isAuthorized($authKey);
        $resultJson  = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if ($authData["code"] == 1) {
            $productId   = $this->helper->validate($wholeData, "productId") ? $wholeData["productId"] : 0;
            $sellerId    = 0;
            $returnArray->sellerInfo = new \Magento\Framework\DataObject();
            $returnArray->sellerInfo->displaySellerInfo = (bool) $this->helper->getConfigData(
                "marketplace/profile_settings/seller_profile_display"
            );
            $returnArray->sellerInfo->sellerId          = 0;
            $returnArray->sellerInfo->shoptitle         = "";
            $returnArray->sellerInfo->sellerRating      = [];
            $returnArray->sellerInfo->reviewDescription = "";
            $returnArray->sellerInfo->sellerName = "";
            $returnArray->sellerInfo->sellerLocation = "";
            $returnArray->sellerInfo->sellerProductCount = 0;
            $returnArray->sellerInfo->showContactSeller = true;
            $marketplaceProduct = $this->marketplaceHelper->getSellerProductDataByProductId($productId);
            foreach ($marketplaceProduct as $eachProduct) {
                $sellerId = $eachProduct["seller_id"];
            }
            if ($sellerId != 0) {
                $shoptitle        = "";
                $sellerCollection = $this->marketplaceHelper->getSellerDataBySellerId($sellerId);
                foreach ($sellerCollection as $seller) {
                    $shoptitle = $seller["shop_title"];
                    if (!$shoptitle) {
                        $shoptitle = $seller->getShopUrl();
                    }
                }
                $returnArray->sellerInfo->sellerId  = $sellerId;
                $returnArray->sellerInfo->shoptitle = $this->viewTemplate->escapeHtml($shoptitle);
                $returnArray->sellerInfo->sellerAverageRating = $this->marketplaceHelper->getSelleRating($sellerId);
                $feeds = $this->marketplaceHelper->getFeedTotal($sellerId);
                $returnArray->sellerInfo->reviewDescription =
                    (($this->marketplaceHelper->getSelleRating($sellerId)*100)/5).
                    "% ".__("positive feedback")." (".__("%1 ratings", number_format($feeds["feedcount"])).") ";
                $returnArray->sellerInfo->sellerRating[] = [
                    "label" => __("Price"),
                    "value" => round(($feeds["price"]/20), 1, PHP_ROUND_HALF_UP)
                ];
                $returnArray->sellerInfo->sellerRating[] = [
                    "label" => __("Value"),
                    "value" => round(($feeds["value"]/20), 1, PHP_ROUND_HALF_UP)
                ];
                $returnArray->sellerInfo->sellerRating[] = [
                    "label" => __("Quality"),
                    "value" => round(($feeds["quality"]/20), 1, PHP_ROUND_HALF_UP)
                ];

                $returnArray->sellerInfo->sellerProductCount = $this->marketplaceHelper
                    ->getSellerProductCollection($sellerId)->getSize();
                $returnArray->sellerInfo->sellerLocation = $sellerCollection->getFirstItem()
                    ->getData('company_locality');
                $returnArray->sellerInfo->sellerName = $sellerCollection->getFirstItem()->getName();

                $returnArray->reportData = new \Magento\Framework\DataObject();
                $returnArray->reportData->showReportProduct = (bool) $this->helper->getConfigData(
                    "marketplace/product_flag/status"
                );
                $returnArray->reportData->guestCanReport = (bool) $this->helper->getConfigData(
                    "marketplace/product_flag/guest_status"
                );
                $returnArray->reportData->showReportReason = (bool) $this->helper->getConfigData(
                    "marketplace/product_flag/reason"
                );
                $returnArray->reportData->productReportLabel = $this->helper->getConfigData(
                    "marketplace/product_flag/product_flag_label"
                );
                if ($returnArray->reportData->showReportReason) {
                    $returnArray->reportData->showReportOtherReason = (bool) $this->helper->getConfigData(
                        "marketplace/product_flag/other_reason"
                    );
                    $returnArray->reportData->productOtherReasonLabel = $this->helper->getConfigData(
                        "marketplace/product_flag/other_reason_label"
                    );
                    // $reasons = $this->productFlagReason->getCollection();
                    // $returnArray->reportData->productFlagReasons = $reasons->getData();
                }
            }
        } else {
            $resultJson->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_UNAUTHORIZED);
            $resultJson->setHeader("token", $authData["token"], true);
        }
        $resultJson->setData($returnArray);
        return $resultJson;
    }
}
