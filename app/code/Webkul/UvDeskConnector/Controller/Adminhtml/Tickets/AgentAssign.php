<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_UvDeskConnector
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\UvDeskConnector\Controller\Adminhtml\Tickets;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * AgentAssign class
 */
class AgentAssign extends \Magento\Backend\App\Action
{

    /** @var \UvDeskConnector\Model\TicketManager */
    private $ticketManager;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonResultFactory;

    /**
     * __construct function
     *
     * @param \Magento\Backend\App\Action\Context              $context
     * @param \Webkul\UvDeskConnector\Model\TicketManager      $ticketManager
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\UvDeskConnector\Model\TicketManager $ticketManager,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        parent::__construct($context);
        $this->ticketManager = $ticketManager;
        $this->jsonResultFactory = $jsonResultFactory;
    }

    public function execute()
    {
        $result = $this->jsonResultFactory->create();
        $ticketId = $this->getRequest()->getParam('ticketid');
        $agentId = $this->getRequest()->getParam('agentId');
        $tickets = $this->ticketManager->assignAgentToTicket($ticketId, $agentId);
        return $result->setData($tickets);
    }
    
    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_UvDeskConnector::tickets');
    }
}
