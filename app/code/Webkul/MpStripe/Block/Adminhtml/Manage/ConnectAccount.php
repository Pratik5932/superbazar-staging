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
namespace Webkul\MpStripe\Block\Adminhtml\Manage;

use Webkul\MpStripe\Model\StripeSellerFactory;

class ConnectAccount extends \Magento\Backend\Block\Template
{
    /**
     * Block template.
     *
     * @var string
     */
    protected $_template = 'connectaccount.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param StripeSellerFactory $stripeSeller
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        StripeSellerFactory $stripeSeller,
        \Webkul\MpStripe\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->stripeSeller = $stripeSeller;
        $this->helper = $helper;
    }

    /**
     * to get stripe seller information from stripe
     *
     * @return array
     */
    public function getStripeSellerInformation()
    {
        $stripeAccount = '';
        $sellerId = $this->getRequest()->getParam('seller_id');
        $sellerData = $this->stripeSeller->create()
        ->getCollection()->addFieldToFilter('seller_id', $sellerId)->getFirstItem();
        $this->helper->setUpDefaultDetails();
        if ($sellerData->getStripeUserId()) {
            $stripeAccount = \Stripe\Account::retrieve($sellerData->getStripeUserId());
        }
        return $stripeAccount;
    }

    /**
     * consent message
     *
     * @return string
     */
    public function getConsentMessage()
    {
        $serviceAgreement = __('Services Agreement');
        $connectedAccountAgreement = __("Connected Account Agreement");
        $remainingMsg = __('certify that the information you have provided is complete and correct');
        return __(
            "By creating account, you agree to our %1, %2, and %3",
            '<a href="https://stripe.com/gb/legal" target="__blank">'.$serviceAgreement.'</a>',
            '<a href="https://stripe.com/gb/connect-account/legal" target="__blank">'.$connectedAccountAgreement.'</a>',
            $remainingMsg
        );
    }
}
