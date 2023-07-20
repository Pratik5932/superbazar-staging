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
namespace Webkul\UvDeskConnector\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Index class
 */
abstract class AbstractController extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * __construct function
     *
     * @param Context $context
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        
        parent::__construct($context);
    }

    /**
     * Check customer is logged in or not ?
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $dataHelper = $objectManager->get('Webkul\UvDeskConnector\Helper\Data');
        if (!$customerSession->authenticate()) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        if (!$dataHelper->getAvilabilityOfUvdesk()) {
            if ($request->getFullActionName() == 'uvdeskcon_createticket_index') {
                $this->messageManager->addError(
                    __("The UvDesk module is disable from the configuration. Please contact Admin")
                );
                $this->_redirect('');
            }
            $this->resultFactory->create('forward')->forward('index/index');
        }
        return parent::dispatch($request);
    }
}
