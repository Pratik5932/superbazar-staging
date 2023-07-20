<?php

namespace Superbazaar\General\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class CustomerDataProvider extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [], 
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    
    public function  prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item)
            {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $customer = $objectManager->create('Magento\Customer\Model\Customer')->load($item['customer_id']);
                $shippingAddressId = $customer->getDefaultShipping();
                $address = $objectManager->get('Magento\Customer\Model\AddressFactory')->create()->load($shippingAddressId);
                $item[$this->getData('name')] = $address->getData('postcode');
            }
        }

        return $dataSource;
    }
}

