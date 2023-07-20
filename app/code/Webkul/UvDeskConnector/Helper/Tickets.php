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

namespace Webkul\UvDeskConnector\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;

/**
 * Tickets class
 */
class Tickets extends AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    private $resourceConfig;
    
    /**
     * @var \Webkul\UvDeskConnector\Model\TicketManager
     */
    private $ticketManager;

    /**
     * __construct function
     *
     * @param \Magento\Framework\App\Helper\Context       $context
     * @param \Magento\Config\Model\ResourceModel\Config  $resourceConfig
     * @param \Magento\Customer\Model\Session             $customerSession
     * @param \Webkul\UvDeskConnector\Model\TicketManager $ticketManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\UvDeskConnector\Model\TicketManager $ticketManager
    ) {
        $this->resourceConfig = $resourceConfig;
        $this->customerSession = $customerSession;
        $this->ticketManager = $ticketManager;
        parent::__construct($context);
    }

    /**
     * Return the ticket data after formatting it with proper key value pair.
     *
     * @return Array.
     */
    public function formatData($tickets = [])
    {
        if (isset($tickets['error'])) {
            return $tickets;
        }
        if (isset($tickets['response']['error'])) {
            return $tickets;
        }
        $paginationData = [];
        $ticketData = [];
        $ticketThreadData = [];
        $ticketThreadPaginationData = [];
        $ticketPriorityData = [];
        $ticketStatusData = [];
        $tabData = [];
        $data = [];
        $allAgent = $this->ticketManager->getFilterDataFor('agent');
        $tickets = json_decode(json_encode($tickets), true);
        if (isset($tickets['tickets']) && !empty($tickets['tickets'])) {
            foreach ($tickets['tickets'] as $ticket) {
                    $temp['priority'] = $ticket['priority']['name'];
                    $temp['priority_color'] = $ticket['priority']['color'];
                    $temp['incrementId'] = $ticket['incrementId'];
                    $temp['id'] = $ticket['id'];
                    $temp['name'] = $ticket['customer']['name'];
                    $temp['subject'] = $ticket['subject'];
                    $temp['creation_date'] = $ticket['formatedCreatedAt'];
                    $temp['replies'] = $ticket['totalThreads'];
                    $temp['agent'] = $ticket['agent']['name'];
                if (isset($ticket['status'])) {
                    $temp['status'] = ['name'=> $ticket['status']['name'], 'color'=>$ticket['status']['color']];
                }
                    $ticketData[] = $temp;
                    $temp = [];
            }
        }
        if (isset($tickets['status']) && !empty($tickets['status'])) {
            foreach ($tickets['status'] as $status) {
                    $temp['tab_name'] = $status['name'];
                    $temp['tab_id'] = $status['id'];
                    $temp['tab_count'] = $tickets['tabs'][$status['id']];
                    $tabData[] = $temp;
                    $temp = [];
            }
        }
        if (isset($tickets['pagination']) && !empty($tickets['pagination'])) {
                    $temp['first'] = $tickets['pagination']['first'];
                    $temp['last'] = $tickets['pagination']['last'];
                    $temp['previous'] = 0;
            if (isset($tickets['pagination']['previous'])) {
                $temp['previous'] = $tickets['pagination']['previous'];
            }
                    $temp['next'] = 0;
            if (isset($tickets['pagination']['next'])) {
                $temp['next'] = $tickets['pagination']['next'];
            }
            $temp['firstPageInRange'] = $tickets['pagination']['firstPageInRange'];
            $temp['lastPageInRange'] = $tickets['pagination']['lastPageInRange'];
            $temp['pagesInRange'] = $tickets['pagination']['pagesInRange'];
            $temp['pageCount'] = $tickets['pagination']['pageCount'];
            $paginationData[] = $temp;
            $temp = [];
        }
        if (isset($tickets['threads']) && !empty($tickets['threads'])) {
            foreach ($tickets['threads'] as $thread) {
                        $temp['id'] = $thread['id'];
                        $temp['name'] = $thread['user']['detail'][$thread['userType']]['name'];
                        $temp['userSmallThumbNail'] = $thread['user']['smallThumbnail'];
                        $temp['customerDetail'] = $thread['user']['detail'][$thread['userType']]['name'];
                        $temp['userType'] = $thread['userType'];
                        $temp['reply'] = $thread['reply'];
                        $temp['formatedCreatedAt'] = $thread['formatedCreatedAt'];
                        $ticketThreadData[] = $temp;
                        $temp = [];
            }
            $ticketThreadPaginationData['currentPage'] = $tickets['pagination']['current'];
            $ticketThreadPaginationData['lastPage'] = $tickets['pagination']['last'];
            $ticketThreadPaginationData['next'] = isset($tickets['pagination']['next']) ?
                                                  $tickets['pagination']['next'] : 0;
            $ticketThreadPaginationData['numItemsPerPage'] = $tickets['pagination']['numItemsPerPage'];
            $ticketThreadPaginationData['totalCount'] = $tickets['pagination']['totalCount'];
        }
        if (isset($tickets['priority']) && !empty($tickets['priority'])) {
            foreach ($tickets['priority'] as $priority) {
                        $temp['id'] = $priority['id'];
                        $temp['name'] = $priority['name'];
                        $ticketPriorityData[] = $temp;
                        $temp = [];
            }
        }
        if (isset($tickets['status']) && !empty($tickets['status'])) {
            foreach ($tickets['status'] as $status) {
                        $temp['id'] = $status['id'];
                        $temp['name'] = $status['name'];
                        $ticketStatusData[] = $temp;
                        $temp = [];
            }
        }
        $data['ticket_data'] = $ticketData;
        $data['tab_data'] = $tabData;
        $data['pagination_data'] = $paginationData;
        $data['agent-information'] = $allAgent;
        $data['ticket_thread']['thread'] = $ticketThreadData;
        $data['ticket_thread']['pagination'] = $ticketThreadPaginationData;
        $data['priority'] = $ticketPriorityData;
        $data['status'] = $ticketStatusData;
        return $data ;
    }

    /**
     * Return the logged in user details.
     *
     * @return Array.
     */
    public function getLoggedInUserDetail()
    {
        $customerDetal = [];
        $customerDetal['entity_id'] = $this->customerSession->getCustomer()->getEntityId();
        $customerDetal['email'] = $this->customerSession->getCustomer()->getEmail();
        $customerDetal['name'] = $this->customerSession->getCustomer()->getName();
        return $customerDetal;
    }

    /**
     * ticketValidation function
     *
     * @param string $ticketIncrementId
     * @return boolean
     */
    public function ticketValidation($ticketIncrementId = "")
    {
        if ($ticketIncrementId == "") {
            return false;
        }
        $ticketInformation = $this->ticketManager->getSingleTicketData($ticketIncrementId);
        if ($ticketInformation == false) {
            return false;
        }
        if (is_array($ticketInformation)) {
            $ticketCustomerId = $ticketInformation['ticket']['customer']['id'];
            $ticketCustomerEmail = $ticketInformation['ticket']['customer']['email'];
            $loggedInCustomerId = $this->customerSession->getCustomerUvdeskId();
            $loggedInCustomerEmail = $this->customerSession->getCustomer()->getEmail();
            if (!($ticketCustomerId == $loggedInCustomerId && $ticketCustomerEmail == $loggedInCustomerEmail)) {
                return false;
            }
            return true;
        }
        return false;
    }
}
