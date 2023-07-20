<?php
namespace Superbazaar\CustomWork\Plugin;

class Product
{
	private $request;
	
	private $productModel;
	
	private $customer;
	
	private $scopeConfig;
	
	private $backendSession;
	
	private $customerFactory;
	
	public function __construct(
		\Magento\Backend\Model\Session\Quote $backendSession,
		\Magento\Catalog\Model\ProductFactory $productModel,
		\Magento\Framework\App\RequestInterface $request,
		\Magento\Customer\Model\SessionFactory $customerSession,
		\Magento\Customer\Model\CustomerFactory $customerFactory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	) {
		$this->backendSession = $backendSession;
		$this->request = $request;
		$this->productModel = $productModel;
		$this->customer = $customerSession;
		$this->scopeConfig = $scopeConfig;
		$this->customerFactory = $customerFactory;
	}
    public function afterGetPrice(\Magento\Catalog\Model\Product $product, $result)
    {
		if($customerId = $this->backendSession->getCustomerId()) {
			$customer = $this->customerFactory->create()->load($customerId);
		} else {
			$customer = $this->customer->create()->getCustomer();
		}
		if ($customer->getEntityId() && $this->request->getFullActionName() != 'marketplace_product_edit') {
			try {
				$currentCustomerGroupId = $customer->getGroupId();
				$configCustomerGroupId = $this->scopeConfig->getValue('general/settings/customer_group', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
				$prevOrderCost = $this->productModel->create()->getResource()->getAttributeRawValue($product->getId(), 'previous_order_cost_price', 0);
				if ($prevOrderCost && $currentCustomerGroupId == $configCustomerGroupId) {
					$percentageFee = $this->scopeConfig->getValue('general/settings/percentage', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
					return $prevOrderCost + (($prevOrderCost * $percentageFee) / 100);
				}
			} catch (\Exception $e) {
				
			}
		}
		return $result;
    }
}