<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mppaypalexpresscheckout
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mppaypalexpresscheckout\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_Marketplace::menu');
        $resultPage->addBreadcrumb(
            __('Paypal Express Checkout Seller Details'),
            __('Paypal Express Checkout Seller Details')
        );
        $resultPage->getConfig()->getTitle()->prepend(
            __('Paypal Express Checkout Seller Details')
        );
        return $resultPage;
    }

    /**
     * Check the permission to view Mercadopago details
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Mppaypalexpresscheckout::list');
    }
}
