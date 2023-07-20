<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ZipCodeValidator
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\ZipCodeValidator\Controller\Adminhtml\Region;

use Webkul\ZipCodeValidator\Controller\Adminhtml\Region as RegionController;
use Magento\Framework\Controller\ResultFactory;

class Edit extends RegionController
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Webkul\ZipCodeValidator\Model\RegionFactory
     */
    protected $_region;

    /**
     * @param \Magento\Backend\App\Action\Context            $context
     * @param \Magento\Framework\Registry                    $registry
     * @param \Webkul\ZipCodeValidator\Model\RegionFactory   $region
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Webkul\ZipCodeValidator\Model\RegionFactory $region
    ) {
        $this->_backendSession = $context->getSession();
        $this->_registry = $registry;
        $this->_region = $region;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $region = $this->_region->create();
        if ($this->getRequest()->getParam('id')) {
            $region->load($this->getRequest()->getParam('id'));
        }
        $data = $this->_backendSession->getFormData(true);
        if (!empty($data)) {
            $region->setData($data);
        }
        $this->_registry->register('zipcodevalidator', $region);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Region Entries'));
        $resultPage->getConfig()->getTitle()->prepend(
            $region->getId() ? $region->getTitle() : __('New Entry')
        );
        $block = 'Webkul\ZipCodeValidator\Block\Adminhtml\Region\Edit';
        $content = $resultPage->getLayout()->createBlock($block);
        $resultPage->addContent($content);
        return $resultPage;
    }
}
