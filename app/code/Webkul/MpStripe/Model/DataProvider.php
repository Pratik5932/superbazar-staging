<?php
namespace Webkul\MpStripe\Model;

use Webkul\MpStripe\Model\ResourceModel\StripeSeller\CollectionFactory;
use Webkul\MpStripe\Model\Source\IntegrationType;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    protected $_loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $stripeCollectionFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $stripeCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Webkul\MpStripe\Helper\Data $helper,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\App\RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $stripeCollectionFactory->create();
        $this->customerFactory = $customerFactory;
        $this->helper = $helper;
        $this->_coreSession = $coreSession;
        $this->request = $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $this->helper->setUpDefaultDetails();

        $sellerStripeData = $this->collection;
        $items = $this->collection->getItems();
        foreach ($items as $stripeSeller) {
            $this->_loadedData[$stripeSeller->getSellerId()] = $stripeSeller->getData();
        }
        $sellerId = $this->request->getParam('seller_id');
        $customer = $this->customerFactory->create()->load($sellerId);
        if ($this->_coreSession->getSellerCustomAccountData() != null) {
            $this->_loadedData[$sellerId]['stripe_user'] = $this->_coreSession->getSellerCustomAccountData();
        }
        if (!empty($this->_loadedData[$sellerId]) && !empty($this->_loadedData[$sellerId]['integration_type'])) {
            if ($this->_loadedData[$sellerId]['integration_type'] == IntegrationType::STRIPE_CONNECT_CUSTOM) {
                $stripeAccount = \Stripe\Account::retrieve($this->_loadedData[$sellerId]['stripe_user_id']);
                $externalAccount = $stripeAccount['external_accounts']['data'][0];
                $this->_loadedData[$sellerId]['stripe_user'] = $stripeAccount;
                $this->_loadedData[$sellerId]['stripe_user']['account_id'] = $stripeAccount['id'];
                $this->_loadedData[$sellerId]['stripe_user']['external_accounts'] = $externalAccount;
                $this->_loadedData[$sellerId]['stripe_user']['external_accounts']
                ['account_number'] = $externalAccount['last4'];
            }
        }
        
        $this->_loadedData[$sellerId]['stripe_user']['email'] = $customer->getEmail();
        $this->_loadedData[$sellerId]['stripe_user']['user_id'] = $customer->getId();
        
        return $this->_loadedData;
    }
}
