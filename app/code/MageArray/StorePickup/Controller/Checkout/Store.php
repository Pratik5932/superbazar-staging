<?php

namespace MageArray\StorePickup\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Store extends Action
{

    /**
     * Store constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation
     * @param MageArray\StorePickup\Model\StoreFactory $storeFactory
     * @param Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation,
        \MageArray\StorePickup\Model\StoreFactory $storeFactory,
        \MageArray\StorePickup\Helper\Data $dataHelper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->countryInformation = $countryInformation;
        $this->_storeFactory = $storeFactory;
        $this->_dataHelper = $dataHelper;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        $html = '';
        $result = [];
        try {
            $storeColl = $this->_storeFactory->create()->load($post['storeId']);
            $disableDate = $this->_dataHelper->disablePickupDate();
            if (count($storeColl->getData()) > 0) {
                $country = $this->countryInformation
                    ->getCountryInfo($storeColl->getCountry());
                $countryName = $country->getFullNameLocale();
                $address = $storeColl->getAddress() . ", " . $storeColl->getCity() . ", " . $storeColl->getState() . " " . $storeColl->getZipcode() . ", " . $countryName;
                $html .= "<div><h4>" . $storeColl->getStoreName() . "</h4><p>Store Address: " . $address . "</p>
				<p>Phone Number: " . $storeColl->getPhoneNumber() . "</p><p>Working Hours: " . $storeColl->getWorkingHours() . "</p></div>";
            }
            $result['html'] = $html;
            $result['success'] = 1;
            $result['disable_date'] = $disableDate;
            $result['working_days'] = $storeColl->getOpeningDays();
            $this->_checkoutSession->setData("store_session", $post['storeId']);
        } catch (\Exception $e) {
            $result['success'] = 0;
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
