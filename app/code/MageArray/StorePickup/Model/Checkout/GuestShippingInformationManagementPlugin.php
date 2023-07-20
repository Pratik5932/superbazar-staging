<?php

namespace MageArray\StorePickup\Model\Checkout;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Psr\Log\LoggerInterface as Logger;
use Magento\Quote\Model\QuoteAddressValidator;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Model\ShippingAssignmentFactory;
use Magento\Quote\Model\ShippingFactory;
use Magento\Framework\App\ObjectManager;

class GuestShippingInformationManagementPlugin extends \Magento\Checkout\Model\GuestShippingInformationManagement
{

    public function __construct(
     \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Checkout\Api\ShippingInformationManagementInterface $shippingInformationManagement
         
    ) {

        parent::__construct(
        $quoteIdMaskFactory,
        $shippingInformationManagement
        );

        
         $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->shippingInformationManagement = $shippingInformationManagement;

    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function saveAddressInformation(
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        //echo $quoteIdMask->getQuoteId();die;
        return $this->shippingInformationManagement->saveAddressInformation(
            $quoteIdMask->getQuoteId(),
            $addressInformation
        );
    }
}

    
