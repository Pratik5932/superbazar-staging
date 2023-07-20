<?php
namespace Superbazaar\CustomWork\Plugin;

use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as MpProductCollection;

class Banktransfer
{
	private $backendSession;

	protected $checkoutSession;
	
	protected $mpProCollection;
	
	public function __construct(
		\Webkul\Marketplace\Helper\Data $mpHelper,
		\Magento\Checkout\Model\Session $checkoutSession,
		MpProductCollection $mpProCollection,
		\Magento\Backend\Model\Session\Quote $backendSession
	) {
		$this->mpHelper = $mpHelper;
		$this->checkoutSession = $checkoutSession;
		$this->mpProCollection = $mpProCollection;
		$this->backendSession = $backendSession;
	}
    public function afterGetInstructions(\Magento\OfflinePayments\Model\Banktransfer $subject, $result)
    {
		$bankDetails = "";
		$items = $this->checkoutSession->getQuote()->getAllVisibleItems();
		if (empty($items)) {
			$items = $this->checkoutSession->getLastRealOrder()->getAllVisibleItems();
		}
		if (empty($items)) {
			$items = $this->backendSession->getQuote()->getAllVisibleItems();
		}
		if (empty($items)) {
			$items = $this->backendSession->getOrder()->getAllVisibleItems();
		}
		if (!empty($items)) {
			foreach ($items as $item) {
				$productId = $item->getProductId();
				$sellerId = $this->mpProCollection->create()
					->addFieldToFilter('mageproduct_id', $productId)
					->setPageSize(1)
					->getFirstItem()
					->getSellerId();
				if (!empty($sellerId)) {
					$bankDetails = $this->mpHelper->getSellerCollectionObj($sellerId)->setPageSize(1)
						->getFirstItem()
						->getBankDetails();
					if (!empty($bankDetails)) {
						return $bankDetails;
					}
				}
				break;
			}
		}
		return $result;
    }
}