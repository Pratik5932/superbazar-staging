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
 * DownloadAttachment class
 */
class DownloadAttachment extends \Magento\Backend\App\Action
{
    /** @var \Webkul\UvDeskConnector\Model\TicketManager */
    private $ticketManager;
    
    /** @var \Magento\Framework\App\Response\Http\FileFactory */
    private $fileFactory;
    
    /** @var \Magento\Framework\Controller\Result\RawFactory */
    private $resultRawFactory;

    /**
     * __construct function
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Webkul\UvDeskConnector\Model\TicketManager $ticketManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Webkul\UvDeskConnector\Model\TicketManager $ticketManager
    ) {
    
        parent::__construct($context);
        $this->ticketManager        = $ticketManager;
        $this->resultRawFactory      = $resultRawFactory;
    }

    public function execute()
    {
        $attachmenId = $this->getRequest()->getParam('attachment_id');
        $name = $this->getRequest()->getParam('name');
        $file = $this->ticketManager->downloadAttachment($attachmenId);
        header('Content-Disposition: attachment; filename="'.$name.'"');
        header('Content-Type: '.$file['info']['content_type']);
        header('Content-Length: ' . strlen($file['response']));
        header('Connection: close');
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($file['response']); //set content for download file here
        return $resultRaw;
    }

    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_UvDeskConnector::tickets');
    }
}
