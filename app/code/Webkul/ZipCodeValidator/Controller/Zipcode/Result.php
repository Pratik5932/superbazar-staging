<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ZipCodeValidator
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\ZipCodeValidator\Controller\Zipcode;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

class Result extends Action
{
    /**
     * @var \Webkul\ZipCodeValidator\Model\ResourceModel\Zipcode\CollectionFactory
     */
    protected $zipcodeCollection;

    /**
     * CookieManager
     *
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var Webkul\ZipCodeValidator\Helper
     */
    protected $helper;

    /**
     * @param Context $context
     * @param CookieManagerInterface $cookieManager
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\ZipCodeValidator\Model\ResourceModel\Zipcode\CollectionFactory $zipcodeCollection
     * @param \Webkul\ZipCodeValidator\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        CookieManagerInterface $cookieManager,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Customer\Model\Session $session,
        \Webkul\ZipCodeValidator\Model\ResourceModel\Zipcode\CollectionFactory $zipcodeCollection,
        \Webkul\ZipCodeValidator\Helper\Data $helper
    ) {
        $this->zipcodeCollection = $zipcodeCollection;
        $this->customerUrl = $customerUrl;
        $this->cookieManager = $cookieManager;
        $this->session = $session;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = [];
        $data['addesses'] = '';
        $data['url'] = '';
        try {
            $address = $this->getAllAddressOfCustomer();

            if ($address) {
                $data['addesses'] = $address;
            } elseif (!$this->session->getCustomerId()) {
                $data['url'] = $this->customerUrl->getLoginUrl();
            }

            $zip = $this->getRequest()->getParam('zip');
            $productId = $this->getRequest()->getParam('productId');
            $cookie = $this->cookieManager->getCookie('mpzip');

            $regionIds = $this->helper->validateZipCode($productId);
            if (!empty($regionIds)) {
                $model = $this->zipcodeCollection->create()
                    ->addFieldToFilter('region_zipcode_from', array('lteq' => $zip))
                    ->addFieldToFilter('region_zipcode_to', array('gteq' => $zip))
                    ->addFieldToFilter('region_id', ['in', $regionIds]);
                if (count($model)) {
                    $data['product_zipcode'] = $zip;
                    $data['product_id'] = $productId;
                }
            }
            
            if ($cookie) {
                $data['cookieZip'] = $cookie;
                $cookiezip = trim($zip).','.$data['cookieZip'];
            } else {
                $cookiezip = trim($zip);
            }

            $this->cookieManager->setPublicCookie('mpzip', $cookiezip);
        } catch (\Excpetion $e) {
            $this->helper->logDataInLogger("Excpetion Controller_Zipcode_Result_execute : ".$e->getMessage());
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($data);
        return $resultJson;
    }

    /**
     * Get all address of logged Customer
     *
     * @return array
     */
    public function getAllAddressOfCustomer()
    {
        $customerAddress = [];
        try {
            if ($this->session->getCustomerId()) {
                $customer = $this->session->getCustomer();
                foreach ($customer->getAddresses() as $address) {
                    $addr = $address->toArray();
                    $postcode = $addr['postcode'];
                    $street = $addr['street'];
                    $city = $addr['city'];
                    if ($street && $city && $postcode) {
                        $custAddr = $postcode.' '.$street.' '.$city;
                    }
                    $customerAddress[] = substr($custAddr, 0, 20).'...';
                }
            }
        } catch (\Excpetion $e) {
            $this->helper->logDataInLogger("Excpetion Controller_Zipcode_Result_getAllAddressOfCustomer : ".$e->getMessage());
        }
        return $customerAddress;
    }
}
