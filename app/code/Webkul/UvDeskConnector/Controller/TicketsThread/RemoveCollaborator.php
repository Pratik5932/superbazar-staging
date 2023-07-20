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

namespace Webkul\UvDeskConnector\Controller\TicketsThread;

use Webkul\UvDeskConnector\Controller\AbstractController;

class RemoveCollaborator extends AbstractController
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
     * @param \Magento\Framework\Controller\Result\JsonFactory    $jsonResultFactory
     * @param \Webkul\UvDeskConnector\Model\TicketManagerCustomer $ticketManagerCustomer
     * @param \Webkul\UvDeskConnector\Helper\Tickets              $ticketsHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Webkul\UvDeskConnector\Model\TicketManagerCustomer $ticketManagerCustomer,
        \Webkul\UvDeskConnector\Helper\Tickets $ticketsHelper
    ) {
    
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->ticketManagerCustomer = $ticketManagerCustomer;
        $this->ticketsHelper = $ticketsHelper;
        parent::__construct($context, $resultPageFactory);
    }

    public function execute()
    {
        $result = $this->jsonResultFactory->create();
        $successCount = 0;
        $errorCount = 0;
        $post = $this->getRequest()->getParams();
        if ((isset($post['ticketId']) &&
            !empty($post['ticketId'])) &&
            (isset($post['collaboratorId']) &&
            !empty($post['collaboratorId']))
        ) {
            $response = $this->ticketManagerCustomer->removeCollaborater($post['ticketId'], $post['collaboratorId']);
            return $result->setData($response);
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
