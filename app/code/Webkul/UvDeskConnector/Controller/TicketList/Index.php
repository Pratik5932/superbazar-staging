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
namespace Webkul\UvDeskConnector\Controller\TicketList;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\UvDeskConnector\Controller\AbstractController;

/**
 * Index class
 */
class Index extends AbstractController
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    
    /**
     * @var \Webkul\UvDeskConnector\Model\TicketManagerCustomer
     */
    private $ticketManagerCustomer;
    
    /**
     * @var \Webkul\UvDeskConnector\Helper\Tickets
     */
    private $ticketsHelper;
    
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonResultFactory;
    
    /**
     * __construct function
     *
     * @param Context                                             $context
     * @param PageFactory                                         $resultPageFactory
     * @param \Magento\Customer\Model\Session                     $customerSession
     * @param \Webkul\UvDeskConnector\Model\TicketManagerCustomer $ticketManagerCustomer
     * @param \Webkul\UvDeskConnector\Helper\Tickets              $ticketsHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory    $jsonResultFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\UvDeskConnector\Model\TicketManagerCustomer $ticketManagerCustomer,
        \Webkul\UvDeskConnector\Helper\Tickets $ticketsHelper,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->ticketManagerCustomer = $ticketManagerCustomer;
        $this->ticketsHelper = $ticketsHelper;
        $this->jsonResultFactory = $jsonResultFactory;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $result = $this->jsonResultFactory->create();
        $post = $this->getRequest()->getParams();
        $page = $this->checkStatus('pageNo');
        $label = $this->checkStatus('labels');
        $tab = $this->checkStatus('tab');
        $agent = $this->checkStatus('agent');
        $customer = $this->checkStatus('customer');
        $group = $this->checkStatus('group');
        $team = $this->checkStatus('team');
        $priority = $this->checkStatus('priority');
        $type = $this->checkStatus('type');
        $tag = $this->checkStatus('tag');
        $mailbox = $this->checkStatus('mailbox');
        $status = $this->checkStatus('status');
        $sort = $this->checkStatus('sort');
        if (isset($post['isAjax'])) {
            $customerUvdeskId = $this->customerSession->getCustomerUvdeskId();
            $tickets = $this->ticketManagerCustomer->getAllTickets(
                $page,
                $label,
                $tab,
                $agent,
                $customerUvdeskId,
                $group,
                $team,
                $priority,
                $type,
                $tag,
                $mailbox,
                $status,
                $sort
            );
            $formatedTickets = $this->ticketsHelper->formatData($tickets);
            return $result->setData($formatedTickets);
        } else {
            return $resultPage;
        }
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
}
