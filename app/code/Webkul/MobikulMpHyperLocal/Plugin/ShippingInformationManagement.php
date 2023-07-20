<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulMpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MobikulMpHyperLocal\Plugin;

use Magento\Framework\Exception\StateException;

class ShippingInformationManagement
{
    /**
     * @var \Webkul\MpHyperLocal\Helper\Data
     */
    private $hyperLocalHelper;

    public function __construct(
        \Magento\Directory\Model\Country $country,
        \Webkul\MpHyperLocal\Helper\Data $hyperLocalHelper
    ) {
        $this->country = $country;
        $this->hyperLocalHelper = $hyperLocalHelper;
    }

    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $this->customerId = $this->getRequest()->getPostValue('customerId');
        $this->quoteId = $this->getRequest()->getPostValue('quoteId');
        
        $quote = new \Magento\Framework\DataObject();
        if ($this->customerId != 0) { 
            $quote = $this->helper->getCustomerQuote($this->customerId);
        }
        if ($this->quoteId != 0) {
            $quote = $this->helper->getQuoteById($this->quoteId)->setStoreId($this->storeId);
        }    
        $isEnabled = $this->hyperLocalHelper->isEnabled();
        if ($isEnabled) {   
            $shipAddress = $this->hyperLocalHelper->getSavedAddress();
            if ($shipAddress) {  
                $quoteShipAdd =  $quote->getShippingAddress()->getData();
                unset($quoteShipAdd['extension_attributes']);
                $quoteShipAdd['country'] = $this->country->load($quoteShipAdd['country_id'])->getName();
                $quoteShipAdd['street'] = trim(preg_replace('/\s+/', ' ', $quoteShipAdd['street']));
                $quoteShipAdd = str_replace(',', ' ', implode(" ", $quoteShipAdd));
                $quoteShipAdd = explode(' ', strtolower($quoteShipAdd));
                $shipAddress['address'] = str_replace(
                    strrchr($shipAddress['address'], ','),
                    '',
                    $shipAddress['address']
                );  
                $shipAddress = explode(' ', str_replace(', ', ' ', $shipAddress['address']));
                foreach ($shipAddress as $value) {
                    if (in_array(strtolower($value), $quoteShipAdd) === false) {
                        throw new StateException(__('Shipping address is not same as address selected.'));
                    }
                }
            } else {  
                throw new StateException(__('Please select your address.'));
            }
        }
    }
}
