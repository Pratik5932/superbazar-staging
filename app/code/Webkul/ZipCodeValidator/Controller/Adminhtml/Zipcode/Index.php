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
namespace Webkul\ZipCodeValidator\Controller\Adminhtml\Zipcode;

use Webkul\ZipCodeValidator\Controller\Adminhtml\Zipcode as ZipcodeController;
use Magento\Framework\Controller\ResultFactory;

class Index extends ZipcodeController
{
    /**
     * @var \Webkul\ZipCodeValidator\Model\Region
     */
    protected $_region;

    /**
     * @param \Magento\Backend\App\Action\Context     $context
     * @param \Webkul\ZipCodeValidator\Model\Region   $region
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\ZipCodeValidator\Model\Region $region
    ) {
        $this->_region = $region;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $regionId = $this->getRequest()->getParam('region_id');
        $regionName = $this->_region->load($regionId)->getRegionName();
        $resultPage->getConfig()->getTitle()->prepend(__($regionName.' Zipcode List'));
        return $resultPage;
    }
}
