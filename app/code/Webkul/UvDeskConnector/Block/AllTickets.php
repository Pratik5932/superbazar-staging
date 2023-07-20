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

namespace Webkul\UvDeskConnector\Block;

/**
 * AllTickets class
 */
class AllTickets extends \Magento\Framework\View\Element\Template
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\UvDeskConnector\Helper\Tickets           $ticketHelper
     * @param \Webkul\UvDeskConnector\Model\TicketManagerCustomer      $ticketManagerCustomer
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\UvDeskConnector\Helper\Tickets $ticketHelper,
        \Webkul\UvDeskConnector\Model\TicketManagerCustomer $ticketManagerCustomer,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_ticketHelper = $ticketHelper;
        $this->_ticketManagerCustomer = $ticketManagerCustomer;
        $this->_customerSession = $customerSession;
    }

    /**
     * getLoggedInUserDetail function get the logged in user details
     *
     * @return array
     */
    public function getLoggedInUserDetail()
    {
        $customerDetal = $this->_ticketHelper->getLoggedInUserDetail();
        return $customerDetal;
    }

    /**
     * getTicketsAccToCustomer function get the tickets acc to log in customers
     *
     * @return array
     */
    public function getTicketsAccToCustomer()
    {
        $customerUvdeskId = $this->_customerSession->getCustomerUvdeskId();
        $agent = $this->checkStatus('agent');
        $page = $this->checkStatus('pageNo');
        $label = $this->checkStatus('labels');
        $tab = $this->checkStatus('tab');
        $customer = $this->checkStatus('customer');
        $group = $this->checkStatus('group');
        $team = $this->checkStatus('team');
        $priority = $this->checkStatus('priority');
        $type = $this->checkStatus('type');
        $tag = $this->checkStatus('tag');
        $mailbox = $this->checkStatus('mailbox');
        $status = $this->checkStatus('status');
        $sort = $this->checkStatus('sort');
        if (!isset($customerUvdeskId)) {
            $customerData = [];
            $customerData['email'] = $this->_customerSession->getCustomer()->getEmail();
            $customerData['firstName'] = $this->_customerSession->getCustomer()->getFirstname();
            $customerData['lastName'] = $this->_customerSession->getCustomer()->getLastname();
            $customerData['contactNumber'] = "";
            $customerData['isActive'] = 1;
            $customerUvDeskData = $this->_ticketManagerCustomer->createCustomerAtUveDesk($customerData);
            if (isset($customerUvDeskData['id'])) {
                $this->_customerSession->setCustomerUvdeskId($customerUvDeskData['id']);
            } else {
                return $customerUvDeskData;
            }
        }
        $tickets = $this->_ticketManagerCustomer->getAllTickets(
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
            $mailbox
        );
        return $this->_ticketHelper->formatData($tickets);
    }

    /**
     * checkStatus function check the value set in the params array
     *
     * @param string $code
     * @return string|null
     */
    public function checkStatus($code)
    {
        $flag = $this->getRequest()->getParam($code);
        if (isset($flag)) {
            return $this->getRequest()->getParam($code);
        } else {
            return null;
        }
    }

    /**
     * getTicketsTypes function get the all ticket types from Uvdesk
     *
     * @return void
     */
    public function getTicketsTypes()
    {
        $tickets = $this->_ticketManagerCustomer->getTicketTypes();
        return $tickets;
    }

    /**
     * getErrorMessage function get the error message acc to theresponse comes from Uvdesk  end
     *
     * @param array $tickets
     * @return string
     */
    public function getErrorMessage($tickets = [])
    {
        if (isset($tickets['error_description'])) {
            return $tickets['error_description']." Please contact administration.";
        }
        if (isset($tickets['error']) && $tickets['error']!==1 && $tickets['error']!==0) {
            return $tickets['error']." Please contact administration.";
        }
        return __("Some thing went wrong in the Uvdesk configuration. Please contact the administration");
    }

    /**
     * getAllAgents function get the all agents of Uvdesk account
     *
     * @return array
     */
    public function getAllAgents()
    {
        $tickets = $this->_ticketManagerCustomer->getAllMembers();
        return $tickets;
    }
}
