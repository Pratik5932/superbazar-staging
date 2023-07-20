<?php
namespace Superbazaar\General\Ui\Component\Listing\Column;
 
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;
 
class Postcode extends Column
{
    protected $_customerRepository;
    protected $_searchCriteria;
    protected $_pannumberfactory;
 
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $criteria,
        array $components = [],
        array $data = []
    ) {
        $this->_customerRepository = $customerRepository;
        $this->_searchCriteria  = $criteria;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
              #  $customer  = $this->_customerRepository->getById($item["entity_id"]);
 
                $item[$this->getData('name')] = $item['billing_postcode'];
            }
        }
        return $dataSource;
    }
}