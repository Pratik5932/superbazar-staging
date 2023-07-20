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
namespace Webkul\UvDeskConnector\Model;

use Webkul\UvDeskConnector\Logger\UvdeskLogger;

/**
 * TicketManager class
 */
class TicketManager
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
        UvdeskLogger $uvdeskLogger,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {

        $this->helperData = $helperData;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $uvdeskLogger;
        $this->curl = $curl;
    }

    /**
     * Curl request to get all tickets of UvDesk.
     *
     * @return json.
     */
    public function getAllTicketsAccToLabel($label, $isLabelId = false)
    {
        $access_token = $this->getAccessToken();
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        if (!$isLabelId) {
            $url = 'https://'.$company_domain.'.uvdesk.com/en/api/tickets.json?'.$label;
        } else {
            $url = 'https://'.$company_domain.'.uvdesk.com/en/api/tickets.json?label='.$label;
        }
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
                'Modal TicketManager getAllTicketsAccToLabel :- ',
                ['response'=>$response, 'info'=> $this->curl->getHeaders()]
            );
            if (isset($response['error'])) {
                return $response;
            }
        }
    }

    /**
     * Curl request to get all tickets of UvDesk.
     *
     * @return json.
     */
    public function getAllTickets(
        $page = null,
        $labels = null,
        $labelId = null,
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
        $str = $this->getParameterStringForGetAllTickets(
            $page,
            $labels,
            $labelId,
            $tab,
            $agent,
            $customer,
            $group,
            $team,
            $priority,
            $type,
            $tag,
            $mailbox,
            $status,
            $sort
        );
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
                'Modal TicketManager getAllTickets :- ',
                ['response'=> $response, 'info'=> $this->curl->getHeaders()]
            );
            if (isset($response['error'])) {
                return $response;
            }
        }
    }

    /**
     * getParameterStringForGetAllTickets to get teh parameter in in string
     *
     * @return json.
     */
    public function getParameterStringForGetAllTickets(
        $page = null,
        $labels = null,
        $labelId = null,
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
        } else {
            if (isset($labelId)) {
                $str.="&label=".$labelId;
            }
        }
        return $str;
    }

    /**
     * Curl request to get all data for filters of UvDesk.
     *
     * @return Json.
     */
    public function getFilterDataFor($filterType)
    {
        $access_token = $this->getAccessToken();
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/filters.json?'.$filterType.'=1';
        $this->curl->addHeader('Authorization', 'Bearer '.$access_token);
        $arr = [
            CURLOPT_CUSTOMREQUEST => "GET",
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
                'Modal TicketManager getFilterDataFor :- ',
                ['response'=> $response, 'info'=> $this->curl->getHeaders()]
            );
            if (isset($response['error'])) {
                return $response;
            }
        }
    }

    /**
     * Curl request to get the customer detail via email from UvDesk.
     *
     * @return  Json.
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
                'Modal TicketManager getCustomerFromEmail :- ',
                ['response'=> $response, 'info'=> $this->curl->getHeaders()]
            );
            return false;
        }
    }

    /**
     * Curl request to get the ticket types in UvDesk.
     *
     * @return  Json.
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
                'Modal TicketManager getTicketTypes :- ',
                ['response'=> $response, 'info'=> $this->curl->getHeaders()]
            );
            return "";
        }
    }

    /**
     * Curl request to get the ticket getTicketThread in UvDesk.
     *
     * @return  JSON.
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
            if (isset($response['error'])) {
                $this->log(
                    'Modal TicketManager getTicketThread :- ',
                    ['response'=> $response, 'info'=> $this->curl->getHeaders()]
                );
                return $response;
            }
            return [
                'error'=>true,
                'error_description'=> __('There is some error in getting the thread. Please check log.')
            ];
        }
    }

    /**
     * Curl request to get the information of single tickets in UvDesk.
     *
     * @return  JSON.
     */
    public function getSingleTicketData($ticketIncrementId)
    {
        $access_token = $this->getAccessToken();
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
                'Modal TicketManager getSingleTicketData :- ',
                ['response'=> $response, 'info'=> $this->curl->getHeaders()]
            );
            return false;
        }
    }

    /**
     * Curl request to add a reply to a tickets in UvDesk.
     *
     * @return  String.
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
        $this->curl->addHeader('Content-type', 'multipart/form-data; boundary=' .$mime_boundary);
        $arr = [
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_SSLVERSION => 1,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
        ];
        $this->curl->setOptions($arr);
        $this->curl->post($url, $data);
        $response = $this->getJsonDecodeResponse($this->curl->getBody());
        if (in_array($this->curl->getStatus(), [100,200,201])) {
             $this->messageManager->addSuccess(__('Success ! Reply added successfully.'));
             return true;
        } else {
            $this->log(
                'Modal TicketManager addReplyToTicket :- ',
                ['response'=> $response, 'info'=> $this->curl->getHeaders()]
            );
            $message = $this->helperData->getErrorMessage($response);
            $this->messageManager->addError(__($message));
            return false;
        }
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
                'Modal TicketManager downloadAttachment :- ',
                ['response'=>$response, 'info'=> $this->curl->getHeaders()]
            );
            return false;
        }
    }

    /**
     * Curl request to delete the tickets in UvDesk.
     *
     * @return  String.
     */
    public function trashTicket()
    {
        $access_token = $this->getAccessToken(1);
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $url = 'https://'.$company_domain.'.uvdesk.com/en/ /api/ticket/4802/trash.json';
        $this->curl->addHeader('Authorization', 'Bearer '.$access_token);
        $arr = [
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_POSTFIELDS => $data
        ];
        $this->curl->setOptions($arr);
        $this->curl->get($url);
        $response = $this->curl->getBody();
        if ($this->curl->getStatus() == 200) {
            return ['response'=>$response,'info'=> $this->curl->getStatus()];
        } else {
            $response = $this->getJsonDecodeResponse($this->curl->getBody());
            $this->log(
                'Modal TicketManager trashTicket :- ',
                ['response'=>$response, 'info'=> $this->curl->getHeaders()]
            );
            return false;
        }
    }

    /**
     * Curl request to change the agent of a ticket in UvDesk.
     *
     * @return  String.
     */
    public function assignAgentToTicket($ticketId, $agentId)
    {
        $access_token = $this->getAccessToken(1);
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $data = '{"id": "'.$agentId.'"}';
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/ticket/'.$ticketId.'/agent.json';
        $this->curl->addHeader('Authorization', 'Bearer '.$access_token);
        $arr = [
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_POSTFIELDS => $data
        ];
        $this->curl->setOptions($arr);
        $this->curl->get($url);
        $response = $this->curl->getBody();
        if ($this->curl->getStatus() == 200) {
            return ['response'=>$response,'info'=> $this->curl->getStatus()];
        } else {
            $this->log(
                'Modal TicketManager assignAgentToTicket :- ',
                ['response'=>$response, 'info'=> $this->curl->getHeaders()]
            );
            return false;
        }
    }

    /**
     * Curl request to delete the tickets in UvDesk.
     *
     * @return  String.
     */
    public function deleteTicket($ticketIds)
    {
        $access_token = $this->getAccessToken(1);
        if (isset($access_token['error'])) {
            return $access_token;
        }
        $company_domain = $this->getCompanyDomainName();
        if (isset($company_domain['error'])) {
            return $company_domain;
        }
        $ids['ids'] = $ticketIds;
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/tickets.json';
        $this->curl->addHeader('Authorization', 'Bearer '.$access_token);
        $this->curl->addHeader('content-type', 'application/json');
        $arr = [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_POSTFIELDS => $this->getJsonEncodeResponse($ids)
        ];
        $this->curl->setOptions($arr);
        $this->curl->get($url);
        if ($this->curl->getStatus() == 200) {
            return ['response'=>true];
        } else {
            $response = $this->getJsonDecodeResponse($this->curl->getBody());
            $this->log(
                'Modal TicketManager deleteTicket :- ',
                ['response'=> $response, 'info'=> $this->curl->getHeaders()]
            );
            return ['response'=> false];
        }
    }

    /**
     * Curl request to get all members of UvDesk.
     *
     * @return  String.
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
        }
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/members.json?fullList=name';
        $this->curl->addHeader('Authorization', 'Bearer '.$access_token);
        $this->curl->addHeader('content-type', 'application/json');
        $arr = [
            CURLOPT_CUSTOMREQUEST => 'GET',
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
                'Modal TicketManager getAllMembers :- ',
                ['response'=> $response, 'info'=> $this->curl->getHeaders()]
            );
            if (isset($response['error'])) {
                return $response;
            }
            $message = $this->helperData->getErrorMessage($response);
            $this->messageManager->addError(__($message));
            return ['error'=> 'true'];
        }
    }

    /**
     * checkCredentials function check the credentials are correct or not before save in configuration
     *
     * @param string $access_token
     * @param string $company_domain
     * @return bool
     */
    public function checkCredentials($access_token = "", $company_domain = "")
    {
        if (preg_match('/^\*+$/', $access_token)) {
            $access_token = $this->helperData->getAccessToken();
        }
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/tickets.json';
        $this->curl->addHeader('Authorization', 'Bearer '.$access_token);
        $arr = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false
        ];
        $this->curl->setOptions($arr);
        $this->curl->get($url);
        if ($this->curl->getStatus() == 200) {
            return true;
        } else {
            $response = $this->getJsonDecodeResponse($this->curl->getBody());
            $this->log(
                'Modal TicketManager checkCredentials :- ',
                ['response'=> $response, 'info'=> $this->curl->getHeaders()]
            );
            return false;
        }
    }

    /**
     * getTheDetailOfEnteredTokenAgent function check the credentials have admin level access
     *
     * @param string $access_token
     * @param string $company_domain
     * @return bool
     */
    public function getTheDetailOfEnteredTokenAgent($access_token = "", $company_domain = "")
    {
        if (preg_match('/^\*+$/', $access_token)) {
            $access_token = $this->helperData->getAccessToken();
        }
        $url = 'https://'.$company_domain.'.uvdesk.com/en/api/me.json';
        $this->curl->addHeader('Authorization', 'Bearer '.$access_token);
        $arr = [
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false
        ];
        $this->curl->setOptions($arr);
        $this->curl->get($url);
        $response = $this->getJsonDecodeResponse($this->curl->getBody());
        $flag = 0;
        if ($this->curl->getStatus() == 200) {
            foreach ($response['roles'] as $data) {
                if ($data == 'ROLE_SUPER_ADMIN' || $data == 'ROLE_ADMIN') {
                    $flag = 1;
                    break;
                }
            }
            return $flag;
        } else {
            $this->log(
                'Modal TicketManager getTheDetailOfEnteredTokenAgent :- ',
                ['response'=>$response, 'info'=>$info]
            );
            return $flag;
        }
    }

    /**
     * get the json decoded data of reponse
     *
     * @param string $reponse
     * @return array
     */
    public function getJsonDecodeResponse($reponse = "")
    {
        return $this->jsonHelper->jsonDecode($reponse);
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
        return [
            'error'=> true,
            'error_description'=> 'The access token field is blank. Please provide the access token'
        ];
    }

    /**
     * getCompanyDomainName function get the access token from configuration.
     *
     * @return string|array
     */
    public function getCompanyDomainName()
    {
        if ($this->helperData->getCompanyDomainName()) {
            return $this->helperData->getCompanyDomainName();
        }
        return [
            'error'=> true,
            'error_description'=> 'The company domain name field is blank. Please provide the company domain'
        ];
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
