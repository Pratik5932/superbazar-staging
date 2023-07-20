<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Controller\Linkedin;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\SocialSignup\Helper\Data;
use Magento\Framework\Session\Generic;

/**
 * request class of linkedin
 */
class Request extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var   Data
     */
    protected $_dataHelper;
    /**
     * @var Generic
     */
    protected $_session;
    /**
     * @var LinkedinClient
     */
    protected $_linkedinClient;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $data
     * @param Generic $session
     * @param LinkedinClient $linkedinClient
     */
    public function __construct(
        Context $context,
        Data $data,
        Generic $session,
        LinkedinClient $linkedinClient,
        PageFactory $resultPageFactory
    ) {
    
        $this->_dataHelper = $data;
        $this->_session = $session;
        $this->_linkedinClient = $linkedinClient;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    /**
     * get authenticatio url
     */
    public function execute()
    {
        $this->_session->unsIsSocialSignupCheckoutPageReq();
        $this->_linkedinClient->setParameters();
        $csrf= hash('sha256', uniqid(rand(), true));
        $this->_session->setLinkedinCsrf($csrf);
        $this->_linkedinClient->setState($csrf);

        if (!($this->_linkedinClient->isEnabled())) {
            $this->_dataHelper->redirect404($this);
        }
        $post = $this->getRequest()->getParams();
        $mainwProtocol = $this->getRequest()->getParam('mainw_protocol');
        if (isset($post['is_checkoutPageReq'])) {
            $this->_session->setIsSocialSignupCheckoutPageReq(1);
        }
        $this->_session->setIsSecure($mainwProtocol);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath($this->_linkedinClient->createRequestUrl());
    }
}
