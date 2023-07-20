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
namespace Webkul\MobikulMp\Controller\Product;

/**
 * Class CrosssellProductDataData for getting Cross sell products
 *
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class CrosssellProductData extends AbstractProduct
{
    /**
     * Execute function for class CrosssellProductData
     *
     * @throws LocalizedException
     *
     * @return json | void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "CROSSSELLPRODUCTDATA".$this->storeId.$this->width.$this->pageNumber.$this->filterData;
            $cacheString .= $this->sortData.$this->productId.$this->customerToken.$this->sellerId;

            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $sortData   = $this->jsonHelper->jsonDecode($this->sortData);
            $filterData = $this->jsonHelper->jsonDecode($this->filterData);
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $crosssellProductCollection = $this->relatedProDataProvider->getSellerCollection(
                $this->sellerId,
                $this->storeId,
                'cross_sell'
            );
            $this->returnArray["filterOption"] = [
                [
                    'id'   => 'entity_id',
                    'name' => 'ID',
                    'type' => 'textRange'
                ],
                [
                    'id'   => 'price',
                    'name' => 'Price',
                    'type' => 'textRange'
                ],
                [
                    'id'   => 'name',
                    'name' => 'Name',
                    'type' => 'text'
                ],
                [
                    'id'      => 'attribute_set_id',
                    'name'    => 'Attribute Set',
                    'type'    => 'select',
                    'options' => $this->attributeSetOptions->toOptionArray()
                ],
                [
                    'id'      => 'status',
                    'name'    => 'Status',
                    'type'    => 'select',
                    'options' => $this->status->toOptionArray()
                ],
                [
                    'id'   => 'type_id',
                    'name' => 'Type',
                    'type' => 'select',
                    'options' => $this->productType->toOptionArray()
                ],
                [
                    'id'   => 'sku',
                    'name' => 'SKU',
                    'type' => 'text'
                ]
            ];

            // Filtering product collection
            if (count($filterData) > 0) {
                $filterDataCount = count($filterData[0]);
                for ($i=0; $i<$filterDataCount; ++$i) {
                    if ($filterData[0][$i] != "" && ($filterData[1][$i] == "price" || $filterData[1][$i] == "entity_id")
                    ) {
                        $range = explode("-", $filterData[0][$i]);
                        $to    = $range[1];
                        $from  = $range[0];
                        $crosssellProductCollection->addAttributeToFilter(
                            $filterData[1][$i],
                            [
                                'from' => $from,
                                'to' => $to
                            ]
                        );
                    } elseif ($filterData[0][$i] != "" &&
                        (
                            $filterData[1][$i] == "type_id" ||
                            $filterData[1][$i] == "status" ||
                            $filterData[1][$i] == "attribute_set_id"
                        )
                    ) {
                        $crosssellProductCollection->addAttributeToFilter($filterData[1][$i], $filterData[0][$i]);
                    } else {
                        $crosssellProductCollection->addAttributeToFilter(
                            $filterData[1][$i],
                            ['like' => '%'.$filterData[0][$i].'%']
                        );
                    }
                }
            }

            // Sorting product collection
            if (count($sortData) > 0) {
                $sortBy = $sortData[0];
                if ($sortData[1] == 1) {
                    $crosssellProductCollection->setOrder($sortBy, "ASC");
                } else {
                    $crosssellProductCollection->setOrder($sortBy, "DESC");
                }
            }
            // Applying pagination
            if ($this->pageNumber >= 1) {
                $this->returnArray["totalCount"] = $crosssellProductCollection->getSize();
                $pageSize = $this->catalogHelper->getPageSize();
                $crosssellProductCollection->setPageSize($pageSize)->setCurPage($this->pageNumber);
            }
            $selectedProductIds = [];
            if ($this->productId) {
                $product = $this->product->create()->load($this->productId);
                foreach ($product->getProductLinks() as $key => $value) {
                    if ($value['link_type'] == 'crosssell') {
                        $productBySku = $this->productRepository->get($value['linked_product_sku']);
                        array_push($selectedProductIds, $productBySku->getId());
                    }
                }
            }

            $crosssellProData = [];
            foreach ($crosssellProductCollection as $product) {
                $product = $this->product->create()->load($product->getEntityId());
                $attributeSetName = $this->attributeSet->get($product->getAttributeSetId())->getAttributeSetName();
                $selected = false;
                if (!empty($selectedProductIds) && in_array($product->getEntityId(), $selectedProductIds)) {
                    $selected = true;
                }
                $crosssellProData[] = [
                    'selected'     => $selected,
                    'entity_id'    => $product->getEntityId(),
                    'thumbnail'    => $this->catalogHelper->getImageUrl($product, $this->width/2.5),
                    'name'         => $product->getName(),
                    'attrinuteSet' => $attributeSetName,
                    'status'       => $this->status->getOptionText($product->getStatus()),
                    'type'         => $this->productType->getOptionText($product->getTypeId()),
                    'sku'          => $product->getSku(),
                    'price'        => strip_tags($this->priceFormat->currency($product->getPrice()))
                ];
            }
            $this->returnArray["productCollectionData"] = $crosssellProData;
            
            $this->returnArray["success"]    = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
            $this->checkNGenerateEtag($cacheString);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray, 1);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->eTag          = $this->wholeData["eTag"]          ?? "";
            $this->storeId       = $this->wholeData["storeId"]       ?? 0;
            $this->width         = $this->wholeData["width"]         ?? 1000;
            $this->pageNumber    = $this->wholeData["pageNumber"]    ?? 1;
            $this->filterData    = $this->wholeData["filterData"]    ?? "[]";
            $this->sortData      = $this->wholeData["sortData"]      ?? "[]";
            $this->productId     = $this->wholeData["productId"]     ?? null;
            $this->customerToken = $this->wholeData["customerToken"] ?? '';
            $this->sellerId    = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->sellerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            } elseif ($this->sellerId != 0) {
                $this->customerSession->setCustomerId($this->sellerId);
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
