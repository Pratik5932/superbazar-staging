<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Controller\Cards;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;
    
    /**
     * @var Magento\Customer\Model\Session
     */
    private $customerSession;

    private $scopeConfig;

    /**
     * @var \Magento\Customer\Model\Url
     */
    private $customerUrl;

    private $helper;
    /**
     * @param Context                         $context
     * @param PageFactory                     $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession   customer session
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        \Webkul\MpStripe\Helper\Data $helper,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->customerUrl = $customerUrl;
        parent::__construct($context);
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->customerSession;
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl =
        $this->customerUrl
        ->getLoginUrl();

        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        if (!$this->helper->getIsActive()) {
            $this
                ->resultFactory
                ->create('forward')
                ->forward('noroute');
        }
        return parent::dispatch($request);
    }

    /**
     * load stripe cards.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $stripeLabel = 'Saved Cards';
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__($stripeLabel));

        return $resultPage;
    }
}
