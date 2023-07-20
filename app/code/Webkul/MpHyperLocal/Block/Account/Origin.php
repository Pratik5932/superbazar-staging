<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpHyperLocal\Block\Account;

use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Webkul\Marketplace\Model\SellerFactory;
use Webkul\MpHyperLocal\Helper\Data as HelperData;

class Origin extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Webkul\Marketplace\Model\SellerFactory
     */
    private $sellerFactory;

    /**
     * @param Session $customerSession,
     * @param Context $context,
     * @param SellerFactory $sellerFactory,
     * @param array $data = []
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        SellerFactory $sellerFactory,
        HelperData $helperData,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->sellerFactory = $sellerFactory;
        $this->helperData = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * getOrigin
     * @return bool|array
     */
    public function getOrigin()
    {
        $sellerId = $this->customerSession->getCustomerId();
        $sellerOrigin = $this->sellerFactory->create()->getCollection()
                                                        ->addFieldToFilter('seller_id', $sellerId)
                                                        ->setPageSize(1)->getFirstItem();
        return $sellerOrigin;
    }

    /**
     * Return GoogleApiKey.
     *
     * @return string
     */
    public function getGoogleApiKey()
    {
        return $this->_scopeConfig->getValue('mphyperlocal/general_settings/google_api_key');
    }

    /**
     * getSaveAction
     * @return string
     */
    public function getSaveAction()
    {
        return $this->getUrl('mphyperlocal/account/origin', ['_secure' => $this->getRequest()->isSecure()]);
    }

    public function getFilter() {
        return $this->helperData->getCollectionFilter();
    }
}
