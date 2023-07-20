<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ZipCodeValidator
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\ZipCodeValidator\Plugin\Quote\Model;

class ShippingMethodManagement
{
    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Webkul\ZipCodeValidator\Model\ResourceModel\Zipcode\CollectionFactory $zipcodeCollection
     * @param \Webkul\ZipCodeValidator\Helper\Data $helper
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Webkul\ZipCodeValidator\Model\ResourceModel\Zipcode\CollectionFactory $zipcodeCollection,
        \Webkul\ZipCodeValidator\Helper\Data $helper
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->zipcodeCollection = $zipcodeCollection;
        $this->helper = $helper;
    }

    public function beforeEstimateByExtendedAddress(
        \Magento\Quote\Model\ShippingMethodManagement $subject,
        $cartId,
        $address
    ) {
        try {
            if ($this->helper->getEnableDisable()) {
                $enteredPostCode = $address->getPostCode();
                $quote = $this->quoteRepository->getActive($cartId);
                foreach ($quote->getAllVisibleItems() as $item) {
                    $productId = $item->getProductId();
                    $available = $this->getProductRegion($productId, $enteredPostCode);

                    if (!$available && $enteredPostCode && $enteredPostCode!=="") {
                        $address->setPostCode('');
                        $address->setCountryId('');
                        return [$cartId, $address];
                    }
                }
            } else {
                return [$cartId, $address];
            }
        } catch (\Excpetion $e) {
            $this->helper->logDataInLogger("Plugin_Quote_Model_ShippingMethodManagement_beforeEstimateByExtendedAddress : ".$e->getMessage());
            return [$cartId, $address];
        }
    }

    public function getProductRegion($id, $zip)
    {
        $result = 1;
        try {
            $regionIds = $this->helper->validateZipCode($id);
            if (!empty($regionIds)) {
                $zipcodeModel = $this->zipcodeCollection->create()
                    ->addFieldToFilter('region_zipcode_from', array('lteq' => $zip))
                    ->addFieldToFilter('region_zipcode_to', array('gteq' => $zip))
                    ->addFieldToFilter('region_id', ['in', $regionIds]);
                $result = $zipcodeModel->getSize();
            }
        } catch (\Excpetion $e) {
            $this->helper->logDataInLogger("Plugin_Quote_Model_ShippingMethodManagement_beforeEstimateByExtendedAddress : ".$e->getMessage());
        }
        return $result;
    }
}
