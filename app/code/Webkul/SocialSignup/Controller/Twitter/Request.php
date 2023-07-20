<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Controller\Twitter;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\SocialSignup\Helper\Data;
use Magento\Framework\Session\Generic;

/**
 * Request class of twitter
 */
class Request extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var Webkul\SocialSignup\Helper\Twitter
     */
    protected $_twitterHelper;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_session;
    
    /**
     * @var Data
     */
    protected $_dataHelper;

    /**
     * @param Context       $context
     * @param Data          $data
     * @param Generic       $session
     * @param TwitterClient $twitterClient
     * @param PageFactory   $resultPageFactory
     */
    public function __construct(
        Context $context,
        Data $data,
        Generic $session,
        TwitterClient $twitterClient,
        PageFactory $resultPageFactory
    ) {
    
        $this->_dataHelper = $data;
        $this->_session = $session;
        $this->_twitterClient = $twitterClient;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * redirect to authentication url
     */
    public function execute()
    {
        $this->_session->unsIsSocialSignupCheckoutPageReq();
        $this->_twitterClient->setParameters();
        if (!($this->_twitterClient->isEnabled())) {
            $this->_dataHelper->redirect404($this);
        }
        $post = $this->getRequest()->getParams();
        $mainwProtocol = $this->getRequest()->getParam('mainw_protocol');
        if (isset($post['is_checkoutPageReq'])) {
            $this->_session->setIsSocialSignupCheckoutPageReq(1);
        }
        
        $this->_session->setIsSecure($mainwProtocol);
        $this->_twitterClient->fetchRequestToken();
    }
}
