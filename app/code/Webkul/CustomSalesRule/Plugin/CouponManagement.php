<?php
namespace Webkul\CustomSalesRule\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class CouponManagement
{
	private $scopeConfig;
	
	/**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    protected $couponFactory;

    protected $ruleFactory;
	
	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory
	) {
		$this->scopeConfig = $scopeConfig;
        $this->quoteRepository = $quoteRepository;
        $this->couponFactory = $couponFactory;
        $this->ruleFactory = $ruleFactory;
	}
    public function aroundSet(\Magento\Quote\Model\CouponManagement $subject, $proceed, $cartId, $couponCode)
    {
        $coupon = $this->couponFactory->create();
            $coupon->load($couponCode, "code");
            $ruleId = $coupon->getRuleId();
            $collection = $this->ruleFactory->create()->getCollection();
            $salesRule = $collection->addFieldToFilter('rule_id', $ruleId)->addFieldToSelect("*");
            $data = $salesRule->getFirstItem();
            if (!$data->getForWebsite()) {
                $this->returnArray["success"] = false;
                throw new CouldNotSaveException(
                    __("You can not use %1 coupon code for website.", $couponCode)
                );
            }
		/** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('The "%1" Cart doesn\'t contain products.', $cartId));
        }
        if (!$quote->getStoreId()) {
            throw new NoSuchEntityException(__('Cart isn\'t assigned to correct store'));
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);

        try {
            $quote->setCouponCode($couponCode);
            $this->quoteRepository->save($quote->collectTotals());
        } catch (LocalizedException $e) {
            throw new CouldNotSaveException(__('The coupon code couldn\'t be applied: ' .$e->getMessage()), $e);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __("The coupon code couldn't be applied. Verify the coupon code and try again."),
                $e
            );
        }
        if ($quote->getCouponCode() != $couponCode) {
            throw new NoSuchEntityException(__("The coupon code isn't valid. Verify the code and try again."));
        }
		//custom code
		$enable = $this->scopeConfig->getValue(
			'general/settings/change_message',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		$couponCodeValue = $this->scopeConfig->getValue(
			'general/settings/coupon_code',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		$customSuccessMessage = $this->scopeConfig->getValue(
			'general/settings/coupon_message',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);

		if ($enable && $couponCodeValue == $couponCode) {
			return $customSuccessMessage;
		}
                    
        return 'Your coupon was successfully applied.';
    }
}