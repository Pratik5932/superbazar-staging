<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class CreateCustomer extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\SocialSignup\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->helper   = $helper;
        $this->resultPageFactory = $resultPageFactory;
        $this->storeManager     = $storeManager;
        $this->customerFactory  = $customerFactory;
        parent::__construct($context);
    }

    /**
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $isCheckoutPageReq = 0;
            $session = $this->helper->getFromSession();
            $isCheckoutPageReq = $this->helper->getCoreSession()->getIsSocialSignupCheckoutPageReq();
            if (!empty($session) && is_array($session)) {
                $params = $this->getRequest()->getParams();
                
                $params = array_merge($params, $session);
                $params['website_id'] = $this->storeManager->getStore()->getWebsiteId();

                $customer   = $this->customerFactory->create();
                $customerId = $customer->setData($params)->save()->getId();
                $customer->sendNewAccountEmail();
            }
        } catch (\Exception $e) {
            $this->helper->getCoreSession()->unsIsSocialSignupCheckoutPageReq();
            $this->helper->getLogger()->info('Controller CreateCustomer : '.$e->getMessage());
        }
        if (!$isCheckoutPageReq) {
            $this->messageManager->addSuccess(
                __('You have successfully logged in using %1.', $session['label'])
            );
        }
        $this->helper->clearSession();
        $this->helper->getCoreSession()->unsIsSocialSignupCheckoutPageReq();
        if ($customerId && $customer) {
            if (isset($session['fb_id'])) {
                $data = [
                    'customer_id' => $customerId,
                    'fb_id'     => $session['fb_id']
                ];
                $socialSignupTb = $this->helper->getFacebookTbInstace();
                $socialSignupTb->setData($data)->save();
                $this->helper->getCustomerSession()->setCustomerAsLoggedIn($customer);
                return $this->resultRedirectFactory->create()->setPath(
                    'customer/account/login',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            } else {
                $this->helper->getCustomerSession()->setCustomerAsLoggedIn($customer);
                $this->helper->_loginFinalize($this);
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
