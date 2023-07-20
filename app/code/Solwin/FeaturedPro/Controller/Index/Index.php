<?php

/**
 * Solwin Infotech
 * Solwin Featured Product Extension
 * 
 * @category   Solwin
 * @package    Solwin_FeaturedPro
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/
 */
namespace Solwin\FeaturedPro\Controller\Index;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;

class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * Core registry
     *
     * @var Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /*
     * Result page factory
     * 
     */
    protected $_resultPageFactory;
    
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param TransportBuilder $transportBuilder
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param StateInterface $inlineTranslation
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     */
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context, 
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
    }

    /*
     * Load list.phtml file in frontend and set breadcrumb
     */

    public function execute() {
        $resultPage = $this->_resultPageFactory->create();
        $this->_objectManager->get('Solwin\FeaturedPro\Helper\Data')
                ->getBreadcrumbs($resultPage);
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}