<?php

namespace Superbazaar\General\Ui\Component\Listing\Column;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class Subscriberstatus extends \Magento\Ui\Component\Listing\Columns\Column {

    protected $_customerRepository;
    protected $_searchCriteria;
    protected $subscriberCollection;


    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $criteria,
        array $components = [],
        array $data = []
    ){
        $this->_customerRepository = $customerRepository;
        $this->_searchCriteria  = $criteria;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource) {      
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $customer  = $this->_customerRepository->getById($item["entity_id"]);

                $customer_id = $customer->getId();
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

                $newsletterCollectionFactory = $objectManager->get('\Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory');
                $collection = $newsletterCollectionFactory->create();
                $collection->addFieldToFilter('customer_id', $customer_id);

                $data = $collection->getFirstItem();

                $item[$this->getData('name')] = $data->getSubscriberStatus();
                $value = "Subscribed";
                $value1 = "Unsubscribed";
                if($data->getSubscriberStatus() == 1){
                    $item[$this->getData('name')] = '<span class="grid-severity-notice"><span>' . $value . '</span></span>';
                }else{
                    $item[$this->getData('name')] = '<span class="grid-severity-critical"><span>' . $value1 . '</span></span>';
                }

            }
          
        }

        return $dataSource;
    }
}