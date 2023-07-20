<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_UvDeskConnector
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\UvDeskConnector\Controller\TicketsThread;

use Webkul\UvDeskConnector\Controller\AbstractController;

/**
 * Index class
 */
class Index extends AbstractController
{
    /** @var \Magento\Framework\View\Result\PageFactory */
    private $resultPageFactory;

    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    private $jsonResultFactory;

    /** @var \UvDeskConnector\Model\TicketManagerCustomer */
    private $ticketManagerCustomer;

    /** @var \Webkul\UvDeskConnector\Helper\Tickets */
    private $ticketsHelper;

    /**
     * __construct function
     *
     * @param \Magento\Backend\App\Action\Context                 $context
     * @param \Magento\Framework\View\Result\PageFactory          $resultPageFactory
     * @param \Webkul\UvDeskConnector\Model\TicketManagerCustomer $ticketManagerCustomer
     * @param \Webkul\UvDeskConnector\Helper\Tickets              $ticketsHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory    $jsonResultFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\UvDeskConnector\Model\TicketManagerCustomer $ticketManagerCustomer,
        \Webkul\UvDeskConnector\Helper\Tickets $ticketsHelper,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
    
        $this->resultPageFactory = $resultPageFactory;
        $this->ticketManagerCustomer = $ticketManagerCustomer;
        $this->ticketsHelper = $ticketsHelper;
        $this->jsonResultFactory = $jsonResultFactory;
        parent::__construct($context, $resultPageFactory);
    }

    public function execute()
    {
        $result = $this->jsonResultFactory->create();
        $page = $this->checkStatus('pageNo');
        $ticketId = $this->checkStatus('ticketId');
        $tickets = $this->ticketManagerCustomer->getTicketThread($page, $ticketId);
        $formatedTickets = $this->ticketsHelper->formatData($tickets);
        return $result->setData([$formatedTickets]);
    }

    /**
     * checkStatus function check the particular field is set in params array or not ?
     *
     * @param string $code
     * @return string|null
     */
    private function checkStatus($code)
    {
        $flag = $this->getRequest()->getParam($code);
        if (isset($flag)) {
            return $this->getRequest()->getParam($code);
        } else {
            return null;
        }
    }
    
    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_UvDeskConnector::tickets');
    }
}
