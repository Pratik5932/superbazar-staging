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
 * AddCollaborater class
 */
class AddCollaborater extends AbstractController
{
    /** @var \Magento\Framework\View\Result\PageFactory */
    private $resultPageFactory;

    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    private $jsonResultFactory;

    /** @var \UvDeskConnector\Model\TicketManagerCustomer */
    private $ticketManagerCustomer;

    /**
     * __construct function
     *
     * @param \Magento\Backend\App\Action\Context                 $context
     * @param \Magento\Framework\View\Result\PageFactory          $resultPageFactory
     * @param \Webkul\UvDeskConnector\Model\TicketManagerCustomer $ticketManagerCustomer
     * @param \Magento\Framework\Controller\Result\JsonFactory    $jsonResultFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\UvDeskConnector\Model\TicketManagerCustomer $ticketManagerCustomer,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
    
        $this->resultPageFactory = $resultPageFactory;
        $this->ticketManagerCustomer = $ticketManagerCustomer;
        $this->jsonResultFactory = $jsonResultFactory;
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
            (isset($post['email']) &&
            !empty($post['email']))
        ) {
                $response = $this->ticketManagerCustomer->addCollaborater(
                    $post['ticketId'],
                    $post['email']
                );
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
