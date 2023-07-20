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
class Cards extends \Magento\Framework\View\Element\Template
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
     * @param \Magento\Framework\View\Element\Template\Context                    $context
     * @param \Webkul\Stripe\Model\ResourceModel\StripeCustomer\CollectionFactory $vacationFactory
     * @param \Magento\Customer\Model\Session                                     $customerSession
     * @param \Webkul\Marketplace\Helper\Data                                     $marketplaceHelper
     * @param \Magento\Framework\Message\ManagerInterface                         $messageManager
     * @param DateTime                                                            $date
     * @param Store                                                               $store
     * @param array                                                               $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpStripe\Model\ResourceModel\StripeCustomer\CollectionFactory $stripeCustomerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MpStripe\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        DateTime $date,
        array $data = []
    ) {
        $this->date = $date;
        $this->messageManager = $messageManager;
        $this->stripeCustomerFactory = $stripeCustomerFactory;
        $this->customerSession = $customerSession;
        $this->mpHelper = $mpHelper;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * getSavedCards get customer saved cards.
     *
     * @return Webkul\Stripe\Model\StripeCustomer
     */
    public function getSavedCards()
    {
        $customerId = $this->mpHelper->getCustomerId();
        $cardData = $this->stripeCustomerFactory->create()
            ->addFieldToFilter('customer_id', ['eq' => $customerId]);
        if ($cardData->getSize() > 0) {
            return $cardData;
        }

        return false;
    }

    public function getStripeHelper()
    {
        return $this->helper;
    }
}
