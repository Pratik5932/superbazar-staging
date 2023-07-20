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
use Magento\Framework\View\Result\PageFactory;

/**
 * Redirect Class of Google
 */
class Redirect extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Webkul\SocialSignup\Helper\Data
     */
    private $helperData;
    
    /**
     * @param Context                          $context
     * @param \Webkul\SocialSignup\Helper\Data $helperData
     * @param PageFactory                      $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Webkul\SocialSignup\Helper\Data $helperData,
        PageFactory $resultPageFactory
    ) {
        $this->helperData = $helperData;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    
    /**
     * redirect to login page
     */
    public function execute()
    {
        $this->helperData->_loginFinalize($this);
    }
}
