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
namespace Webkul\UvDeskConnector\Controller\TicketView;

use Webkul\UvDeskConnector\Controller\AbstractController;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

use Magento\Framework\App\RequestInterface;

/**
 * Webkul UvDeskConnector Landing page Index Controller.
 */
class Index extends AbstractController
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;
    
    /**
     * @var \Webkul\UvDeskConnector\Model\TicketManagerCustomer
     */
    private $ticketManagerCustomer;
    
    /**
     * @var \Webkul\UvDeskConnector\Helper\Tickets
     */
    private $ticketHelper;

    /**
     * @param Context                                             $context
     * @param PageFactory                                         $resultPageFactory
     * @param \Webkul\UvDeskConnector\Model\TicketManagerCustomer $ticketManagerCustomer
     * @param \Webkul\UvDeskConnector\Helper\Tickets              $ticketHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\UvDeskConnector\Model\TicketManagerCustomer $ticketManagerCustomer,
        \Webkul\UvDeskConnector\Helper\Tickets $ticketHelper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->ticketManagerCustomer = $ticketManagerCustomer;
        $this->ticketHelper = $ticketHelper;
        $this->customerSession = $customerSession;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * UvDeskConnector Landing page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultPage = $this->resultPageFactory->create();
        $post = $this->getRequest()->getParams();
        $attachments = $this->getRequest()->getFiles();
        $error = 0;
        $ticketId = isset($post['ticket_id'])?$post['ticket_id']:null;
        if (!$this->ticketHelper->ticketValidation($post['increment_id'])) {
            $this->messageManager->addError(__('The ticket trying to view is not accessible'));
            $resultRedirect->setPath(
                'customer/account/'
            );
            return $resultRedirect;
        }
        $tickeIncrementId = isset($post['increment_id'])?$post['increment_id']:null;
        $reply = isset($post['product']['description'])?$post['product']['description']:null;
        $email = $this->ticketHelper->getLoggedInUserDetail()['email'];
        if (isset($post['addReply']) && $post['addReply'] ==  1) {
            $lineEnd = "\r\n";
            $mime_boundary = md5(time());
            $data = '--' . $mime_boundary . $lineEnd;
            $data .= 'Content-Disposition: form-data; name="reply"' . $lineEnd . $lineEnd;
            $data .= $reply . $lineEnd;
            $data .= '--' . $mime_boundary . $lineEnd;
            $data .= 'Content-Disposition: form-data; name="threadType"' . $lineEnd . $lineEnd;
            $data .= "reply" . $lineEnd;
            $data .= '--' . $mime_boundary . $lineEnd;
            $data .= 'Content-Disposition: form-data; name="status"' . $lineEnd . $lineEnd;
            $data .= "1". $lineEnd;
            $data .= '--' . $mime_boundary . $lineEnd;
            if (isset($attachments['attachment']) && $attachments['attachment'][0]['error'] != 4) {
                foreach ($attachments['attachment'] as $key => $file) {
                    if ($file['error'] == 1) {
                        $error = 1;
                        break;
                    }
                    if ($file['error'] == 4) {
                        continue;
                    }
                    $fileType = $file['type'];
                    $fileName =  $file['name'];
                    $fileTmpName =  $file['tmp_name'];
                    $data .= 'Content-Disposition: form-data; name="attachments[]"; filename="' .
                    addslashes($fileName) . '"' . $lineEnd;
                    $data .= "Content-Type: $fileType" . $lineEnd . $lineEnd;
                    $data .= file_get_contents($fileTmpName) . $lineEnd;
                    $data .= '--' . $mime_boundary . $lineEnd;
                }
            }
            $data .= 'Content-Disposition: form-data; name="actAsType"' . $lineEnd . $lineEnd;
            $data .= 'customer'. $lineEnd;
            $data .= '--' . $mime_boundary . $lineEnd;
            if ($email) {
                $data .= 'Content-Disposition: form-data; name="actAsEmail"' . $lineEnd . $lineEnd;
                $data .= $email . $lineEnd;
                $data .= '--' . $mime_boundary . $lineEnd;
            }
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            if ($error == 1) {
                $this->messageManager->addError(__('Attached file size issue.Please contact admin.'));
                $resultRedirect->setPath(
                    'uvdeskcon/ticketview/index/',
                    ['id' => $ticketId,'increment_id'=>$tickeIncrementId]
                );
                return $resultRedirect;
            }
            $response = $this->ticketManagerCustomer->addReplyToTicket(
                $ticketId,
                $tickeIncrementId,
                $data,
                $mime_boundary
            );
            if (isset($response['error'])) {
                if (isset($response['error_description'])) {
                    $this->messageManager->addError(__($response['error_description']));
                }
                $this->messageManager->addError(__($response['error']));
            }
            $resultRedirect->setPath(
                'uvdeskcon/ticketview/index/',
                ['id' => $ticketId,'increment_id'=>$tickeIncrementId]
            );
            return $resultRedirect;
        }
        return $resultPage;
    }
}
