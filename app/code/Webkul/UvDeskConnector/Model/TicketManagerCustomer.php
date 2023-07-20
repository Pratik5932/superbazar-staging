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
namespace Webkul\UvDeskConnector\Model;

use Webkul\UvDeskConnector\Logger\UvdeskLogger;

/**
 * TicketManagerCustomer class
 */
class TicketManagerCustomer
{
    /**
     * @var \Webkul\UvDeskConnector\Helper\Data
     */
    private $helperData;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Webkul\UvDeskConnector\Logger\UvdeskLogger
     */
    private $logger;

    /**
     * @var \Webkul\UvDeskConnector\Helper\Tickets
     */
    private $ticketHelper;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    private $curl;

    /**
     * __construct function
     *
     * @param \Webkul\UvDeskConnector\Helper\Data         $helperData
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Customer\Model\Session             $customerSession
     * @param \Magento\Framework\Json\Helper\Data         $jsonHelper
     * @param UvdeskLogger                                $uvdeskLogger
     */
    public function __construct(
        \Webkul\UvDeskConnector\Helper\Data $helperData,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\UvDeskConnector\Helper\Tickets $ticketHelper,
        UvdeskLogger $uvdeskLogger,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {

        $this->helperData = $helperData;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->jsonHelper = $jsonHelper;
        $this->ticketHelper = $ticketHelper;
        $this->logger = $uvdeskLogger;
        $this->curl = $curl;
    }

    /**
     * Curl request to download the attachment of a ticket in UvDesk.
     *
     * @return  Json.
     */
    public function downloadAttachment($attachmenId)
    {
        $access_token = $this->getAccessToken(1);
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/ticket/attachment/'.$attachmenId.'.json';
        $this->curl->addHeader('Authorization', 'Bearer '.$access_token);
        $arr = [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false
        ];
        $this->curl->setOptions($arr);
        $this->curl->get($url);
        $response = $this->curl->getBody();
        if ($this->curl->getStatus() == 200) {
            return ['response'=> $response,'info'=> $this->curl->getStatus()];
        } else {
            $response = $this->getJsonDecodeResponse($this->curl->getBody());
            $this->log(
                'Modal TicketManagerCustomer downloadAttachment :- '.
                'Error in download attachment from client end',
                ['response'=>$response, 'info'=> $this->curl->getHeaders()]
            );
            return false;
        }
    }

    /**
     * createTicket create ticket curl request
     *
     * @param [type] $data
     * @param [type] $mime_boundary
     * @return boolean
     */
    public function createTicket($data, $mime_boundary)
    {
        $access_token = $this->getAccessToken(1);
        if (isset($access_token['response']['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['response']['error'])) {
            return $company_domain;
        }
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/tickets.json';
        $this->curl->addHeader('Authorization', 'Bearer '.$access_token);
        $this->curl->addHeader('Content-type', 'multipart/form-data; boundary=' .$mime_boundary);
        $arr = [
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_SSLVERSION => 1,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false

        ];
        $this->curl->setOptions($arr);
        $this->curl->post($url, $data);
        $response = $this->getJsonDecodeResponse($this->curl->getBody());
        if (in_array($this->curl->getStatus(), [100,200,201])) {
            $this->messageManager->addSuccess(__(' Success ! Ticket has been created successfully.'));
            return true;
        } else {
            $this->log(
                'Modal TicketManagerCustomer createCustomerAtUveDesk:- '.
                'There is an error in creating ticket from customer end',
                ['response'=> $response, 'info'=> $this->curl->getHeaders()]
            );
            $this->messageManager->addError(
                __('We are not able to proceed your request. Please contact administration')
            );
        }
    }

    /**
     * getAllTickets function is getting all tickets of uvdesk acc to the filters
     *
     * @param int $page
     * @param int $labels
     * @param int $tab
     * @param int $agent
     * @param int $customer
     * @param int $group
     * @param int $team
     * @param int $priority
     * @param int $type
     * @param int $tag
     * @param int $mailbox
     * @param int $status
     * @param int $sort
     * @return array
     */
    public function getAllTickets(
        $page = null,
        $labels = null,
        $tab = null,
        $agent = null,
        $customer = null,
        $group = null,
        $team = null,
        $priority = null,
        $type = null,
        $tag = null,
        $mailbox = null,
        $status = null,
        $sort = null
    ) {
    
        $access_token = $this->getAccessToken();
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $str = '';
        if (isset($agent)) {
            $str.='&agent='.$agent;
        }
        if (isset($tab)) {
            $str.='&status='.$tab;
        }
        if (isset($customer)) {
            $str.='&customer='.$customer;
        }
        if (isset($page)) {
            $str.='&page='.$page;
        }
        if (isset($group)) {
            $str.='&group='.$group;
        }
        if (isset($team)) {
            $str.='&team='.$team;
        }
        if (isset($priority)) {
            $str.='&priority='.$priority;
        }
        if (isset($type)) {
            $str.='&type='.$type;
        }
        if (isset($tag)) {
            $str.='&tag='.$tag;
        }
        if (isset($mailbox)) {
            $str.='&mailbox='.$mailbox;
        }
        if (isset($status)) {
            $str.='&status='.$status;
        }
        if (isset($sort)) {
            $str.='&sort='.$sort.'&direction=asc';
        }
        if (isset($labels)) {
            $str.="&".$labels;
        }
        $customerEmail = $this->ticketHelper->getLoggedInUserDetail()['email'];
        $str.="&actAsType=customer&actAsEmail=".$customerEmail;
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/tickets.json?'.$str;
        $this->curl->addHeader('Authorization', 'Bearer '.$access_token);
        $arr = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false
        ];
        $this->curl->setOptions($arr);
        $this->curl->get($url);
        $response = $this->getJsonDecodeResponse($this->curl->getBody());
        if ($this->curl->getStatus() == 200) {
            return $response;
        } else {
            $this->log(
                'Modal TicketManagerCustomer getAllTickets :- '.
                'There is some error in getting all tickets at customer end.',
                ['response'=> $response, 'info'=> $this->curl->getHeaders()]
            );
            return $response;
        }
    }

    /**
     * Curl request to get the ticket types in UvDesk.
     *
     * @param integer $ticketId
     * @param integer $pageNo
     * @return array
     */
    public function getTicketThread($pageNo, $ticketId = 0)
    {
        $access_token = $this->getAccessToken();
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $str = "";
        if (isset($pageNo)) {
            $str.='page='.$pageNo;
        }
        if ($pageNo == null) {
            $pageNo = "";
        }
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/ticket/'.$ticketId.'/threads.json?'.$str;
        $this->curl->addHeader('Authorization', 'Bearer '.$access_token);
        $arr = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false
        ];
        $this->curl->setOptions($arr);
        $this->curl->get($url);
        $response = $this->getJsonDecodeResponse($this->curl->getBody());
        if ($this->curl->getStatus() == 200) {
            return $response;
        } else {
            $this->log(
                'Modal TicketManagerCustomer getTicketThread :- '.
                'Due to some issue ticket thread cannot be fetched at customer end.',
                ['response'=> $response, 'info'=> $this->curl->getHeaders()]
            );
            if (isset($response['error'])) {
                return $response;
            }
            return [
                'error'=>true,
                'error_description'=> __(
                    'Due to some issue ticket thread cannot be fetched. Please contact administration.'
                )
            ];
        }
    }

    /**
     * Curl request to get the ticket types in UvDesk.
     *
     * @return array
     */
    public function getTicketTypes()
    {
        $access_token = $this->getAccessToken();
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/ticket-types.json?';
        $this->curl->addHeader('Authorization', 'Bearer '.$access_token);
        $arr = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false
        ];
        $this->curl->setOptions($arr);
        $this->curl->get($url);
        $response = $this->getJsonDecodeResponse($this->curl->getBody());
        if ($this->curl->getStatus() == 200 || $this->curl->getStatus() == 201) {
            return $response;
        } else {
            $this->log(
                'Modal TicketManagerCustomer getTicketTypes :- '.
                'There is some error in getting ticket\'s types at customer end.',
                ['response'=> $response, 'info'=> $this->curl->getHeaders()]
            );
            if (isset($response['error'])) {
                return $response;
            }
            return [
                'error'=>true,
                'error_description'=> __(
                    'There is an error in getting ticket type. Please contact administration.'
                )
            ];
        }
    }

    /**
     * Curl request to get all members of UvDesk.
     *
     * @return  array.
     */
    public function getAllMembers()
    {
        $access_token = $this->getAccessToken(1);
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        };
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/members.json?fullList=name';
        $this->curl->addHeader('Authorization', 'Bearer '.$access_token);
        $this->curl->addHeader('content-type', 'application/json');
        $arr = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false
        ];
        $this->curl->setOptions($arr);
        $this->curl->get($url);
        $response = $this->getJsonDecodeResponse($this->curl->getBody());
        if ($this->curl->getStatus() == 200) {
            return $response;
        } else {
            $this->log(
                'Modal TicketManagerCustomer getAllMembers :- '.
                'Due to some issue cannot get all members at customer end.',
                ['response'=> $response, 'info'=> $this->curl->getHeaders()]
            );
            if (isset($response['error'])) {
                return $response;
            }
            return [
                'error'=>true,
                'error_description'=> __(
                    'Due to some issue cannot get all members. Please contact administration.'
                )
            ];
        }
    }

    /**
     * Curl request to get the information of single tickets in UvDesk.
     *
     * @return  array.
     */
    public function getSingleTicketData($ticketIncrementId)
    {
        $access_token = $this->getAccessToken(1);
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/ticket/'.$ticketIncrementId.'.json';
        $this->curl->addHeader('Authorization', 'Bearer '.$access_token);
        $arr = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false
        ];
        $this->curl->setOptions($arr);
        $this->curl->get($url);
        $response = $this->getJsonDecodeResponse($this->curl->getBody());
        if ($this->curl->getStatus() == 200) {
            return $response;
        } else {
            $this->log(
                'Modal TicketManagerCustomer getSingleTicketData :- '.
                'There is some error in getting the ticket details at customer end',
                ['response'=> $response, 'info'=> $this->curl->getHeaders()]
            );
            if (isset($response['error'])) {
                return $response;
            }
            return [
                'error'=>true,
                'error_description'=> __(
                    'There is some error in getting the ticket details. Please contact administration.'
                )
            ];
        }
    }

    /**
     * createCustomerAtUvDesk function is use to create customer at uvdesk.
     *
     * @param array $customerData
     * @return array
     */
    public function createCustomerAtUveDesk($customerData = [])
    {
        $access_token = $this->getAccessToken();
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/customers.json';
        $this->curl->addHeader('Authorization', 'Bearer '.$access_token);
        $arr = [
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_SSLVERSION => 1,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        ];
        $this->curl->setOptions($arr);
        $this->curl->post($url, $this->getJsonEncodeResponse($customerData));
        $response = $this->getJsonDecodeResponse($this->curl->getBody());
        if ($this->curl->getStatus() == 200 || $this->curl->getStatus() == 201) {
             return $response;
        } else {
            $this->log(
                'Modal TicketManagerCustomer createCustomerAtUveDesk:- '.
                'There is an error in creating customer at uvdesk end',
                ['response'=> $response, 'info'=> $this->curl->getHeaders()]
            );
            if (isset($response['error'])) {
                return $response;
            }
            return "";
        }
    }

    /**
     * Curl request to get the customer detail via email from UvDesk.
     *
     * @param string $customerEmail
     * @return array
     */
    public function getCustomerFromEmail($customerEmail = null)
    {
        $access_token = $this->getAccessToken();
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/customers.json?email='.$customerEmail;
        $this->curl->addHeader('Authorization', 'Bearer '.$access_token);
        $arr = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false
        ];
        $this->curl->setOptions($arr);
        $this->curl->get($url);
        $response = $this->getJsonDecodeResponse($this->curl->getBody());
        if ($this->curl->getStatus() == 200 || $this->curl->getStatus() == 201) {
            return $response;
        } else {
            $this->log(
                'Modal TicketManagerCustomer getCustomerFromEmail :- '.
                'There is an error in getting detail from customer email at customer end',
                ['response'=> $response, 'info'=> $this->curl->getHeaders()]
            );
            if (isset($response['error'])) {
                return $response;
            }
            return "";
        }
    }

    /**
     * Curl request to change the agent of a ticket in UvDesk.
     *
     * @param integer $ticketId
     * @param string $email
     * @return array
     */
    public function addCollaborater($ticketId = null, $email = null)
    {
        $access_token = $this->getAccessToken(1);
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $data = ["email"=>$email];
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/ticket/'.$ticketId.'/collaborator.json';
        $this->curl->addHeader('Authorization', 'Bearer '.$access_token);
        $arr = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false
        ];
        $this->curl->setOptions($arr);
        $this->curl->post($url, $data);
        $response = $this->getJsonDecodeResponse($this->curl->getBody());
        if ($this->curl->getStatus() == 200) {
            return $response;
        } else {
            $this->log(
                'Modal TicketManagerCustomer addCollaborater:- '.
                'There is some error in adding collaborator at customer end.',
                ['response'=> $response, 'info'=> $this->curl->getHeaders()]
            );
            if (isset($response['error'])) {
                return $response;
            }
        }
    }

    /**
     * Curl request to delete the agent of a ticket in UvDesk.
     *
     * @param integer $ticketId
     * @param integer $collaboratorId
     * @return array
     */
    public function removeCollaborater($ticketId = null, $collaboratorId = null)
    {
        $access_token = $this->getAccessToken(1);
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $data = ["collaboratorId"=>$collaboratorId];
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/ticket/'.$ticketId.'/collaborator.json';
        $this->curl->addHeader('Authorization', 'Bearer '.$access_token);
        $this->curl->addHeader('content-type', 'application/json');
        $arr = [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_POSTFIELDS => $this->getJsonEncodeResponse($data)
        ];
        $this->curl->setOptions($arr);
        $this->curl->get($url);
        $response = $this->getJsonDecodeResponse($this->curl->getBody());
        if ($this->curl->getStatus() == 200) {
            return ['response'=> $response, 'info'=> $this->curl->getStatus()];
        } else {
            $this->log(
                'Modal TicketManagerCustomer addReplyToTicket:- '.
                'There is some error in removing collaborator',
                ['response'=>$response, 'info'=> $this->curl->getHeaders()]
            );
            if (isset($response['error'])) {
                return $response;
            }
        }
    }

    /**
     * Curl request to add a reply to a tickets in UvDesk.
     *
     * @param integer $ticketId
     * @param integer $ticketIncrementId
     * @param [type] $data
     * @param [type] $mime_boundary
     * @return array
     */
    public function addReplyToTicket($ticketId, $ticketIncrementId, $data, $mime_boundary)
    {
        $access_token = $this->getAccessToken();
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/ticket/'.$ticketId.'/threads.json';
        $this->curl->addHeader('Authorization', 'Bearer '.$access_token);
        $this->curl->addHeader('content-type', 'multipart/form-data; boundary=' .$mime_boundary);
        $arr = [
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_SSLVERSION => 1,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        ];
        $this->curl->setOptions($arr);
        $this->curl->post($url, $data);
        $response = $this->getJsonDecodeResponse($this->curl->getBody());
        if (in_array($this->curl->getStatus(), [100,200,201])) {
             $this->messageManager->addSuccess(__('Success ! Reply added successfully.'));
             return true;
        } else {
            $this->log(
                'Modal TicketManagerCustomer addReplyToTicket:- '.
                'There is an error in adding reply to ticket at customer end.',
                ['response'=> $response, 'info'=> $this->curl->getHeaders()]
            );
            if (isset($response['error'])) {
                return $response;
            }
            return [
                'error'=>true,
                'error_description'=>__('There is an error in adding reply to ticket')
            ];
        }
    }

    /**
     * get the json decoded data of response
     *
     * @param string $response
     * @return array
     */
    public function getJsonDecodeResponse($response = "")
    {
        return $this->jsonHelper->jsonDecode($response);
    }

    /**
     * get the json encode data of reponse
     *
     * @param string $reponse
     * @return array
     */
    public function getJsonEncodeResponse($reponse = "")
    {
        return $this->jsonHelper->jsonEncode($reponse);
    }

    /**
     * getAccessToken function get the access token from configuration.
     *
     * @return string|array
     */
    public function getAccessToken()
    {
        if ($this->helperData->getAccessToken()) {
            return $this->helperData->getAccessToken();
        }
        $this->log('The access token field in configuration is blank');
        return ['error'=> true, 'error_description'=> __('We cannot proceed your request.')];
    }

    /**
     * getCompanyDomainName function get the domain name of company from configuration.
     *
     * @return string|array
     */
    public function getCompanyDomainName()
    {
        if ($this->helperData->getCompanyDomainName()) {
            return $this->helperData->getCompanyDomainName();
        }
        $this->log('The company domain field in configuration is blank');
        return ['error'=> true, 'error_description'=> __('We cannot proceed your request.')];
    }

    /**
     * log function log the error message.
     *
     * @param string $message
     * @param array $data
     * @return void
     */
    public function log($message = "", $data = [])
    {
        $this->logger->critical($message, $data);
    }
}
