<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Controller\Instagram;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Session\Generic;

/**
 * Request class of instagram
 */
class Request extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_session;
    /**
     * @var instagramClient
     */
    protected $_instagramClient;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Generic $session
     * @param InstagramClient $instagramClient
     */
    public function __construct(
        Generic $session,
        Context $context,
        InstagramClient $instagramClient,
        \Webkul\SocialSignup\Helper\Data $helper,
        PageFactory $resultPageFactory
    ) {
    
        $this->_session = $session;
        $this->_instagramClient = $instagramClient;
        $this->helper = $helper;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * redirect to authentication url
     */
    public function execute()
    {
        $this->_session->unsIsSocialSignupCheckoutPageReq();
        $this->_instagramClient->setParameters();
        $helper = $this->helper;
        // CSRF protection
        $csrf = hash('sha256', uniqid(rand(), true));
        $this->_session->setInstagramCsrf($csrf);
        $this->_instagramClient->setState($csrf);
        if (!($this->_instagramClient->isEnabled())) {
            return $helper->redirect404($this);
        }
        $post = $this->getRequest()->getParams();
        $mainwProtocol = $this->getRequest()->getParam('mainw_protocol');
        if (isset($post['is_checkoutPageReq'])) {
            $this->_session->setIsSocialSignupCheckoutPageReq(1);
        }
        $this->_session->setIsSecure($mainwProtocol);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath($this->_instagramClient->createRequestUrl());
    }
}
