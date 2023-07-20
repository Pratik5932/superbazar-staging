<?php
/**
 * Helper
 *
 * @category  Webkul
 * @package   Webkul_ZipCodeValidator
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\ZipCodeValidator\Helper;

use Magento\Store\Model\ScopeInterface;
use Webkul\ZipCodeValidator\Model\Zipcode;
use Webkul\ZipCodeValidator\Model\ResourceModel\Region\CollectionFactory as RegionCollection;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Webkul\ZipCodeValidator\Logger\Logger $logger
     * @param RegionCollection $regionCollection
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Webkul\ZipCodeValidator\Logger\Logger $logger,
        RegionCollection $regionCollection
    ) {
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->regionCollection = $regionCollection;
        parent::__construct($context);
    }

    public function logDataInLogger($data)
    {
        $this->logger->info($data);
    }

    /**
     * function to get Config Data.
     *
     * @return string
     */
    public function getConfigValue($field = false)
    {
        if ($field) {
            return $this->scopeConfig->getValue(
                'zipcodevalidator/wk_zipcodevalidatorstatus/'.$field,
                ScopeInterface::SCOPE_STORE
            );
        } else {
            return false;
        }
    }

    /**
     * getValues from configuration
     * @return $value
     */
    public function getEnableDisable()
    {
        return $this->getConfigValue('wk_zipcodevalidatorstatus');
    }

    public function getApplyStatus()
    {
        return $this->getConfigValue('applyto');
    }

    public function getConfigRegion()
    {
        return $this->getConfigValue('regions');
    }

    public function validateZipCode($productId)
    {
        $regionIds = [];
        try {
            $product = $this->productRepository->getById($productId);
            $productZipCodeValidation = $product->getZipCodeValidation();

            $applyStatus = $this->getApplyStatus();
            if ($applyStatus) {
                if ($productZipCodeValidation == null) {
                    $product->setZipCodeValidation(Zipcode::DEFAULT_CONFIG);
                    $this->productRepository->save($product);
                    $product = $this->productRepository->getById($productId);
                    $productZipCodeValidation = $product->getZipCodeValidation();
                }
                if ($productZipCodeValidation == Zipcode::DEFAULT_CONFIG) {
                    $availableregions = $this->getConfigRegion();
                    $regionIds = explode(',', $availableregions);
                } elseif ($productZipCodeValidation == Zipcode::PARTICULAR_PRODUCT) {
                    $availableregions = $product->getAvailableRegion();
                    $regionIds = explode(',', $availableregions);
                }
            } else {
                $availableregions = $product->getAvailableRegion();
                if ($availableregions && !empty($availableregions) && $availableregions!=="") {
                    $regionIds = explode(',', $availableregions);
                }
            }

            if (!empty($regionIds)) {
                $enabledRegions = $this->regionCollection->create()
                    ->addFieldToFilter('id', ['in' => $regionIds])
                    ->addFieldToFilter('status', ['eq' => 1])
                    ->addFieldToSelect('id');
                $regionIds = $enabledRegions->getColumnValues('id');
            }
        } catch (\Excpetion $e) {
            $this->logDataInLogger("Excpetion Helper_Data_validateZipCode Excpetion : ".$e->getMessage());
        }
        return $regionIds;
    }
}
