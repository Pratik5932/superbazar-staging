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
use Webkul\MpHyperLocal\Model\ShipAreaFactory;

class AddShipArea extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Webkul\MpHyperLocal\Model\ShipAreaFactory
     */
    private $shipArea;

    /**
     * @param Session $customerSession,
     * @param Context $context,
     * @param ShipAreaFactory $shipArea,
     * @param array   $data = []
     */
    public function __construct(
        Session $customerSession,
        Context $context,
        ShipAreaFactory $shipArea,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->shipArea = $shipArea;
        parent::__construct($context, $data);
    }

    /**
     * getAuctionProduct
     * @return bool|array
     */
    public function getAllShipArea()
    {
        $sellerId = $this->customerSession->getCustomerId();
        $shipAreaColl = $this->shipArea->create()->getCollection()->addFieldToFilter('seller_id', $sellerId);
        return $shipAreaColl;
    }

    /**
     * Return ajax url for button.
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
        return $this->getUrl('mphyperlocal/account/savearea', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * getDeleteUrl
     * @param int $locationId
     * @return string
     */
    public function getDeleteUrl($locationId)
    {
        return $this->getUrl(
            'mphyperlocal/account/deletearea',
            [
                '_secure' => $this->getRequest()->isSecure(),
                'id'=>$locationId
            ]
        );
    }
}
