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
namespace Webkul\UvDeskConnector\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\RequestInterface;
 
class AfterCustomerLoggedIn implements ObserverInterface
{
    /** @var Magento\Framework\App\RequestInterface */
    private $request;

    /** @var \Magento\Customer\Model\Session */
    private $customerSession;

    /** @var \Webkul\UvDeskConnector\Model\TicketManager */
    private $ticketManager;

    /**
     * __construct function
     *
     * @param \Psr\Log\LoggerInterface                    $loggerInterface
     * @param \Magento\Customer\Model\Session             $customerSession
     * @param \Webkul\UvDeskConnector\Model\TicketManager $ticketManager
     * @param RequestInterface                            $requestInterface
     */
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\UvDeskConnector\Model\TicketManager $ticketManager,
        RequestInterface $requestInterface
    ) {
        $this->_logger = $loggerInterface;
        $this->customerSession = $customerSession;
        $this->ticketManager = $ticketManager;
        $this->request = $requestInterface;
    }
    
    /**
     * This is the method that fires when the event runs.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $customerUvDeskId = null;
        $customerData = $observer->getCustomer()->getData();
        $customerEmail = $customerData['email'];
        $controller = $this->request->getControllerName();
        $customerDataUvDesk = $this->ticketManager->getCustomerFromEmail($customerEmail);
        if (!empty($customerDataUvDesk['customers'])) {
            $customerUvDeskId = $customerDataUvDesk['customers'][0]['id'];
        }
        if (in_array($controller, ['account']) && $customerUvDeskId) {
            $this->customerSession->setCustomerUvdeskId($customerUvDeskId);
        }
        return true;
    }
}
