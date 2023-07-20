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

class ProductCollection
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
    ) {
        $this->helper            = $helper;
        $this->request           = $request;
        $this->jsonHelper        = $jsonHelper;
        $this->viewTemplate      = $viewTemplate;
        $this->resultFactory     = $resultFactory;
        $this->marketplaceHelper = $marketplaceHelper;
    }

    /**
     * Plugin afterExecute to add new parameters in the response
     *
     * @param \Webkul\Mobikul\Controller\Catalog\ProductPageData $subject  subject
     * @param obj                                                $response response
     *
     * @return ResultFactory $returnarray
     */
    public function afterExecute(\Webkul\MobikulApi\Controller\Catalog\ProductCollection $subject, $response)
    {
        $returnArray = json_decode($response->getRawData());
        $wholeData   = $this->request->getParams();
        $authKey     = $this->request->getHeader("authKey");
        $authData    = $this->helper->isAuthorized($authKey);
        $resultJson  = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $storeId = $wholeData['storeId'] ?? 1;
        $filterData = $wholeData['filterData'] ?? '[]';
        if ($authData["code"] == 1) {
            $returnArray = $this->sellerProducts($filterData, $returnArray);
            if ($this->marketplaceHelper->allowSellerFilter()) {
                $returnArray->layeredData[] = $this->sellerFilter();
            }
        } else {
            $resultJson->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_UNAUTHORIZED);
            $resultJson->setHeader("token", $authData["token"], true);
        }
        $resultJson->setData($returnArray);
        return $resultJson;
    }

    public function sellerFilter()
    {
        $layerData = [];
        $sellerList = $this->marketplaceHelper->getSellerCollection()
            ->addFieldToFilter('store_id', 0)
            ->addFieldToFilter('is_seller', 1);
        $layerData['code'] = 'seller';
        $layerData['label'] = 'Seller';
        $option = [];
        foreach ($sellerList as $seller) {
            if ($this->marketplaceHelper->getSellerProCount($seller->getSellerId()) > 0) {
                $label = $seller->getShopTitle() ?? $seller->getShopUrl();
                $option[] = [
                    'label'=>$label,
                    'id'=>$seller->getSellerId(),
                    'count'=> $this->marketplaceHelper->getSellerProCount($seller->getSellerId())
                ];
            }
        }
        $layerData['options'] = $option;
        return $layerData;
    }

    public function sellerProducts($filterData, $returnArray)
    {
        $filterData = json_decode($filterData);
        $productList = [];
        if (count($filterData) > 0) {
            $filterCount = count($filterData[0]);
            for ($i=0; $i<$filterCount; ++$i) {
                if ($filterData[0][$i] != "" && $filterData[1][$i] == "seller") {
                    $seller_id = $filterData[0][$i];
                    $products = $this->marketplaceHelper->getSellerProductCollection($seller_id);
                    $productIds = $products->getAllIds();
                    foreach ($returnArray->productList as $product) {
                        if (in_array($product->entityId, $productIds)) {
                            $productList[] = $product;
                        }
                    }
                    $returnArray->productList = $productList;
                    $returnArray->totalCount = count($productList);
                    break;
                }
            }
        }
        return $returnArray;
    }
}
