<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulB2B
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulMp\Controller\Seller;

/**
 * Class Fetch for fetching the CustomerList.
 *
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
class CustomerList extends AbstractSeller
{
    /**
     * Execute function for class CustomerList
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = strtoupper("CreateAccountFormData").$this->storeId.$this->customerToken.$this->email;
            $cacheString .= $this->gender.$this->billing_telephone.$this->name.$this->billing_full.$this->customerId;
            if ($this->mobikulHelper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->store->setCurrentCurrencyCode($this->currency);
            $customerGridFlat = $this->collectionSalesList->create()->getTable('customer_grid_flat');
            $collectionData = $this->collectionSalesList->create()->addFieldToFilter(
                'seller_id',
                $this->customerId
            );

            $collectionData->getSelect()->columns(
                'SUM(actual_seller_amount) AS customer_base_total'
            )->columns(
                'count(distinct(order_id)) AS order_count'
            )->group('magebuyer_id');

            $collectionData->getSelect()->join(
                $customerGridFlat.' as cgf',
                'main_table.magebuyer_id = cgf.entity_id',
                [
                    'name' => 'name',
                    'email' => 'email',
                    'billing_telephone' => 'billing_telephone',
                    'gender' => 'gender',
                    'billing_full' => 'billing_full'
                ]
            );

            $this->applyFilters($collectionData);

            $collectionArray = [];
            foreach ($collectionData as $model) {
                $resultArray["customerName"] = $model->getName();
                $resultArray["customerEmail"] = $model->getEmail();
                $resultArray["customerAddress"] = $model->getBillingFull();
                
                $resultArray["customerTelephone"] = $model->getBillingTelephone();
                $resultArray["customerBaseTotal"] = $this->stripTags(
                    $this->priceFormat->currency($model->getCustomerBaseTotal())
                );
                $resultArray["customerOrderCount"] = $model->getOrderCount();
                $gender = $model->getGender();
                if ($gender == "2") {
                    $resultArray["customerGender"] = __("Female");
                } elseif ($gender == "1") {
                    $resultArray["customerGender"] = __("Male");
                } else {
                    $resultArray["customerGender"] = __("Not Specified");
                }
                $collectionArray[] = $resultArray;
            }
            $this->returnArray["customerList"] = $collectionArray;
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->checkNGenerateEtag($cacheString);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Apply filters
     *
     * @param object $collectionData
     * @return void
     */
    public function applyFilters($collectionData)
    {
        if ($this->email) {
            $collectionData->addFieldToFilter('email', ['like' => "%".$this->email."%"]);
        }
        if ($this->gender) {
            if ($this->gender == "3") {
                $collectionData->addFieldToFilter('gender', ['null' => true]);
            } else {
                $genderFilter = "1";
                if ($this->gender == "2") {
                    $genderFilter = "2";
                }
                $collectionData->addFieldToFilter('gender', $genderFilter);
            }
        }
        if ($this->billing_telephone) {
            $collectionData->addFieldToFilter('billing_telephone', ['like' => '%'.$this->billing_telephone.'%']);
        }
        if ($this->name) {
            $collectionData->addFieldToFilter('name', ['like' => '%'.$this->name.'%']);
        }
        if ($this->billing_full) {
            $collectionData->addFieldToFilter('billing_full', ['like' => '%'.$this->billing_full.'%']);
        }
    }

    /**
     * Function verify Request to authenticate the request
     * Authenticates the request and logs the result for invalid requests
     *
     * @return Json
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->email = $this->wholeData["email"] ?? "";
            $this->gender = $this->wholeData["gender"] ?? "";
            $this->billing_telephone = $this->wholeData["billing_telephone"] ?? "";
            $this->name = $this->wholeData["name"] ?? "";
            $this->billing_full = $this->wholeData["billing_full"] ?? "";
            $this->currency = $this->wholeData['currency'] ?? $this->store->getBaseCurrencyCode();
            $this->customerId = $this->mobikulHelper->getCustomerByToken($this->customerToken);
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Please login to Continue...")
                );
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
