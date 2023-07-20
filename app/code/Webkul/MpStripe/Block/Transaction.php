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
namespace Webkul\MpStripe\Block;

use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * MpStripe block.
 *
 * @author Webkul Software
 */
class Transaction extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    /**
     * @var Webkul\Stripe\Model\ResourceModel\StripeCustomer\CollectionFactory
     */
    private $stripeCustomerFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    private $mpHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\MpStripe\Model\ResourceModel\StripeSeller\CollectionFactory $stripeSellerFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param DateTime $date
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpStripe\Model\ResourceModel\StripeSeller\CollectionFactory $stripeSellerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Webkul\MpStripe\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        DateTime $date,
        array $data = []
    ) {
        $this->date = $date;
        $this->messageManager = $messageManager;
        $this->stripeSellerFactory = $stripeSellerFactory;
        $this->customerSession = $customerSession;
        $this->mpHelper = $mpHelper;
        $this->pricingHelper = $pricingHelper;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * getSavedCards get customer saved cards.
     *
     * @return Webkul\Stripe\Model\StripeCustomer
     */
    public function getSellerTransactions()
    {
        $sellerId = $this->mpHelper->getCustomerId();
        $sellerData = $this->stripeSellerFactory->create()
            ->addFieldToFilter('seller_id', ['eq' => $sellerId])->getFirstItem();
            $this->helper->setUpDefaultDetails();
        $response = \Stripe\Transfer::all(["destination" => $sellerData->getStripeUserId()]);
        return $response['data'];
    }

    public function getPriceHtml($price)
    {
        return $this->pricingHelper->currency(number_format($price, 2), true, false);
    }
}
