<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Controller\Message;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Check extends Action
{

    protected $resultJsonFactory;

    protected $checkoutSession;

    protected $cartData;

    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Checkout\Model\Cart $cartData
    ) {
        $this->checkoutSession = $_checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cartData = $cartData;
        $this->coreSession = $coreSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $errorMsg = $this->coreSession->getErrorMsg();
        $successMsg = $this->coreSession->getSuccessMsg();
        return $resultJson->setData(['status' => true, 'errorMsg'=> $errorMsg, 'successMsg' => $successMsg]);
    }
}
