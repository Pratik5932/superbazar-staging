<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Controller\Google;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Session\Generic;

/**
 * request class of google
 */
class Request extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;
    /**
     * @var \Magento\Framework\Session\Generic
     */
    private $session;
    /**
     * @var googleClient
     */
    private $googleClient;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Generic $session
     * @param googleClient
     */
    public function __construct(
        Generic $session,
        Context $context,
        GoogleClient $googleClient,
        PageFactory $resultPageFactory,
        \Webkul\SocialSignup\Helper\Data $helper
    ) {
    
        $this->session = $session;
        $this->googleClient = $googleClient;
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }
    /**
     * redirect the customer to authentication page
     */
    public function execute()
    {
        $this->session->unsIsSocialSignupCheckoutPageReq();
        $this->googleClient->setParameters();
        $helper = $this->helper;
        // CSRF protection
        $csrf = hash('sha256', uniqid(rand(), true));
        $this->session->setGoogleCsrf($csrf);
        $this->googleClient->setState($csrf);
        if (!($this->googleClient->isEnabled())) {
            return $helper->redirect404($this);
        }
        $post = $this->getRequest()->getParams();
        $mainwProtocol = $this->getRequest()->getParam('mainw_protocol');
        if (isset($post['is_checkoutPageReq'])) {
            $this->session->setIsSocialSignupCheckoutPageReq(1);
        }
        $this->session->setIsSecure($mainwProtocol);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath($this->googleClient->createRequestUrl());
    }
}
