<?php
namespace Vnecoms\Sms\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;

abstract class Action extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;
    
    /**
     * Date filter instance
     *
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $_dateFilter;
    
    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Date $dateFilter
     */
    public function __construct(Context $context, Registry $coreRegistry, Date $dateFilter)
    {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_dateFilter = $dateFilter;
    }
    /**
     * Init action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Vnecoms_Sms::notification'
        )->_addBreadcrumb(
            __('Sms Notification'),
            __('Sms Notification')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Sms Notification'));
        return $this;
    }
    
    /**
     * Is access to section allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Vnecoms_Sms::notification');
    }
}
