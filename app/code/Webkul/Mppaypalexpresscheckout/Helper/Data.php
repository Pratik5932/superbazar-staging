<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mppaypalexpresscheckout
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mppaypalexpresscheckout\Helper;

use Webkul\Mppaypalexpresscheckout\Model\ResourceModel\Mppaypalexpresscheckout\CollectionFactory;

/**
 * Mppaypalexpresscheckout data helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    private $mpHelper;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    private $curl;

    /**
     * @var CollectionFactory
     */
    private $sellerCollectionFactory;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Webkul\Mppaypalexpresscheckout\Logger\Logger
     */
    private $logger;

    /**
     * @var \Webkul\Marketplace\Helper\Payment
     */
    private $mpPaymentHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context                             $context
     * @param \Webkul\Marketplace\Helper\Data                                   $mpHelper
     * @param \Magento\Framework\HTTP\Client\Curl                               $curl
     * @param CollectionFactory                                                 $sellerCollectionFactory
     * @param \Magento\Framework\Session\SessionManager                         $coreSession
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface                 $priceCurrency
     * @param \Magento\Store\Model\StoreManagerInterface                        $storeManager
     * @param \Webkul\Mppaypalexpresscheckout\Logger\Logger                     $logger
     * @param \Webkul\Marketplace\Helper\Payment                                $mpPaymentHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\HTTP\Client\Curl $curl,
        CollectionFactory $sellerCollectionFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Mppaypalexpresscheckout\Logger\Logger $logger,
        \Webkul\Marketplace\Helper\Payment $mpPaymentHelper
    ) {
        $this->mpHelper = $mpHelper;
        $this->curl = $curl;
        $this->sellerCollectionFactory = $sellerCollectionFactory;
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->mpPaymentHelper = $mpPaymentHelper;
        parent::__construct($context);
    }

    public function getCancelUrl($orderId = null)
    {
        try {
            return $this->_urlBuilder->getUrl(
                'checkout/cart/index',
                ['orderid' => $orderId]
            );
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data getCancelUrl : ".$e->getMessage());
        }
    }

    public function getReturnlUrl($quoteId = null, $orderId = null)
    {
        try {
            return $this->_urlBuilder->getUrl(
                'mppaypalexpresscheckout/index/paymentsuccess',
                ['orderid' => $orderId, 'quoteId' => $quoteId]
            );
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data getReturnlUrl : ".$e->getMessage());
        }
    }

    public function getIpnNotificationUrl()
    {
        try {
            return $this->_urlBuilder->getUrl(
                'mppaypalexpresscheckout/index/paymentnotify',
                []
            );
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data getIpnNotificationUrl : ".$e->getMessage());
        }
    }

    public function getMpExpressCheckoutPaymentAppId()
    {
        try {
            $sandboxstatus = $this->getConfigValue('sandbox');
            if ($sandboxstatus == 1) {
                return 'APP-80W284485P519543T';
            } else {
                return $this->getConfigValue('paypal_app_id');
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data getMpExpressCheckoutPaymentAppId : ".$e->getMessage());
        }
    }

    public function getConfigValue($fieldId)
    {
        try {
            return $this->scopeConfig->getValue(
                'payment/mppaypalexpresscheckout/'.$fieldId,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data getConfigValue : ".$e->getMessage());
        }
    }

    public function paypalAccountCheck($id, $fname, $lname)
    {
        try {
            $sandbox = $this->getSandboxStatus();
            $url = trim('https://svcs.'.$sandbox.'paypal.com/AdaptiveAccounts/GetVerifiedStatus');

            $APIRequestFormat = 'NV';
            $APIResponseFormat = 'JSON';

            $bodyparams = [
                'emailAddress' => $id,
                'firstName' => $fname,
                'lastName' => $lname,
                'matchCriteria' => 'NAME',
            ];

            $headers = [
                'X-PAYPAL-SECURITY-USERID' => $this->getConfigValue('api_username'),
                'X-PAYPAL-SECURITY-PASSWORD' => $this->getConfigValue('api_password'),
                'X-PAYPAL-SECURITY-SIGNATURE' => $this->getConfigValue('api_signature'),
                'X-PAYPAL-REQUEST-DATA-FORMAT' => $APIRequestFormat,
                'X-PAYPAL-RESPONSE-DATA-FORMAT' => $APIResponseFormat,
                'X-PAYPAL-APPLICATION-ID' => $this->getMpExpressCheckoutPaymentAppId(),
            ];

            $curlArray = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ];

            $response = $this->getResponseFromCurl(
                $url,
                $bodyparams,
                $headers,
                $curlArray
            );

            return $response;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data paypalAccountCheck : ".$e->getMessage());
        }
    }

    public function getResponseFromCurl(
        $url,
        $params = [],
        $headers = [],
        $options = []
    ) {
        try {
            if (!empty($options)) {
                $this->curl->setOptions($options);
            }
            if (!empty($headers)) {
                $this->curl->setHeaders($headers);
            }
            if (!empty($params)) {
                $this->curl->post($url, $params);
            } else {
                $this->curl->get($url);
            }
            $response = json_decode($this->curl->getBody(), true);
            return $response;
        } catch (\Exception $e) {
            $this->logDataInLogger(
                __("getResponseFromCurl Helper_Data : %1", $e->getMessage())
            );
            return false;
        }
    }

    /**
     * isModuleEnabled checks a given module is enabled or not
     *
     * @param  string $moduleName
     * @return boolean
     */
    public function isModuleEnabled($moduleName)
    {
        try {
            return $this->_moduleManager->isEnabled($moduleName);
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data isModuleEnabled : ".$e->getMessage());
        }
    }

    /**
     * isOutputEnabled checks a given module is enabled or not
     *
     * @param  string $moduleName
     * @return boolean
     */
    public function isOutputEnabled($moduleName)
    {
        try {
            return $this->_moduleManager->isOutputEnabled($moduleName);
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data isOutputEnabled : ".$e->getMessage());
        }
    }

    public function checkModuleEnable()
    {
        try {
            $moduleEnabled = $this->isModuleEnabled('Webkul_Mppaypalexpresscheckout');
            $moduleOutputEnabled = $this->isOutputEnabled('Webkul_Mppaypalexpresscheckout');
            if ($this->getConfigValue('active')
                && $moduleEnabled
                && $moduleOutputEnabled
            ) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data checkModuleEnable : ".$e->getMessage());
        }
    }

    public function getSandboxStatus()
    {
        try {
            $sandbox = "";
            $sandboxstatus = $this->getConfigValue('sandbox');
            if ($sandboxstatus == 1) {
                $sandbox = "sandbox.";
            }
            return $sandbox;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data getSandboxStatus : ".$e->getMessage());
        }
    }

    public function checkPaypalIdExistsOrNot($paypalId, $sellerId)
    {
        try {
            $collection = $this->sellerCollectionFactory->create()
                ->addFieldToFilter(
                    'paypal_id',
                    ['eq' => $paypalId]
                )->addFieldToFilter(
                    'seller_id',
                    ['neq' => $sellerId]
                );
            if ($collection->getSize() > 0) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data checkPaypalIdExistsOrNot : ".$e->getMessage());
        }
    }

    public function getSellerPaypalId($sellerId)
    {
        try {
            if ($sellerId && $sellerId!=="" && $sellerId!==0) {
                $collection = $this->sellerCollectionFactory->create()
                    ->addFieldToFilter(
                        'seller_id',
                        ['eq' => $sellerId]
                    )->addFieldToFilter(
                        'status',
                        ['eq' => \Webkul\Mppaypalexpresscheckout\Model\Mppaypalexpresscheckout::STATUS_ENABLED]
                    );
                if ($collection->getSize()) {
                    foreach ($collection as $data) {
                        if ($data->getPaypalId()) {
                            return $data->getPaypalId();
                        } else {
                            return false;
                        }
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data getSellerPaypalId : ".$e->getMessage());
            return false;
        }
    }

    public function getSellerPaypalEmail($sellerId)
    {
        try {
            if ($sellerId && $sellerId!=="" && $sellerId!==0) {
                $collection = $this->sellerCollectionFactory->create()
                    ->addFieldToFilter(
                        'seller_id',
                        ['eq' => $sellerId]
                    )->addFieldToFilter(
                        'status',
                        ['eq' => \Webkul\Mppaypalexpresscheckout\Model\Mppaypalexpresscheckout::STATUS_ENABLED]
                    );
                if ($collection->getSize()) {
                    foreach ($collection as $data) {
                        if ($data->getPaypalMerchantId()) {
                            return $data->getPaypalMerchantId();
                        } else {
                            return $this->getConfigValue('merchant_id');
                        }
                    }
                } else {
                    return $this->getConfigValue('merchant_id');
                }
            } else {
                return $this->getConfigValue('merchant_id');
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data getSellerPaypalEmail : ".$e->getMessage());
        }
    }

    public function sendNvpRequest($url, $request)
    {
        $curlArray = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_VERBOSE        => true,
            CURLOPT_SSLVERSION     => 6
        ];

        try {
            $this->curl->setOptions($curlArray);
            $this->curl->setTimeout(60);
            $this->curl->post($url, $request);
            $response = $this->curl->getBody();
            return $response;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data sendNvpRequest : ".$e->getMessage());
            return $e->getMessage();
        }
    }

    public function getCartDetail($order)
    {
        try {
            $cartdata = [];
            $commission = 0;
            $taxAmount = 0;
            $sellerTaxAmount = 0;
            $adminTaxAmount = 0;
            $commissionDetail =[];
            $onlyAdminTaxAmount = 0;

            
            /*$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $extra = $objectManager->get("Mageprince\Paymentfee\Helper\Data")->getPaymentFeeExtra();
            $cartQty = count($order->getAllItems());
            $paymentFee = $order->getPaymentFee()/$cartQty;*/
            
            foreach ($order->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($item->getProductType()=="bundle") {
                    $childDiscountAmount =
                    $this->mpPaymentHelper->calculateBundleProductDiscount($item->getId(), $order);
                }
                $childDiscountAmount = ($item->getProductType()=="bundle")
                ?
                $this->mpPaymentHelper->calculateBundleProductDiscount(
                    $item->getId(),
                    $order
                ) : 0;
                $product = $item->getProduct();
                $invoiceprice = $item->getBaseRowTotal();
                $singleItemPrice = $item->getBasePrice();

                $commissionData = $this->mpPaymentHelper->getCommissionData($item);
                $commissionData = $this->updateCommissionData($commissionData);
                $tempcoms = $commissionData['tempcoms'];
                $commissionDetail = $commissionData['commissionDetail'];

                $commission += $tempcoms;
                $price = $invoiceprice - $tempcoms;

                if (!isset($commissionDetail['id'])) {
                    $commissionDetail['id'] = 0;
                }
                // $commissionDetail['id'] = (!isset($commissionDetail['id'])) ? $commissionDetail['id'] : 0;
                $sellerdetails['id'] = $commissionDetail['id'];
                $sellerdetails['comission'] = $commission;
                $productprice = floatval($price);

                if (!$this->mpHelper->getConfigTaxManage()) {
                    $adminTaxAmount += $item->getBaseTaxAmount();
                } else {
                    $sellerTaxAmount = $item->getBaseTaxAmount();
                }
                
                $realSellerId = $this->mpPaymentHelper->getRealSellerId($item);
                $couponAmount = $this->mpPaymentHelper->getSellerCouponAmount($realSellerId);
                $creditPoints = $this->mpPaymentHelper->getCreditPoints($realSellerId);
                $totalDiscountAmount = $couponAmount + $creditPoints;

                $totalSellerAmount = $productprice + $sellerTaxAmount;
                $onlyAdminAmount = $productprice;
                $onlyAdminTaxAmount += $sellerTaxAmount;
                $itemDiscountAmount = $item->getBaseDiscountAmount();

                if ($itemDiscountAmount <= 0 && isset($childDiscountAmount) && $childDiscountAmount > 0) {
                    $itemDiscountAmount = $childDiscountAmount;
                }
                if ($itemDiscountAmount > 0) {
                    $itemDiscountAmount = -$itemDiscountAmount;
                    $totalDiscountAmount += $itemDiscountAmount;
                }

                if (empty($cartdata)) {
                    if ($sellerdetails['id'] == 0) {
                        $adminTaxAmount = ($this->mpHelper->getConfigTaxManage()) ?
                        $adminTaxAmount += $sellerTaxAmount : $adminTaxAmount;
                        $cartdata[$sellerdetails['id']][]=[
                            'name'=>$product->getName(),
                            'number'=>$product->getId(),
                            'sku'=>$product->getSku(),
                            'qty'=>$item->getQty(),
                            'amt'=>$onlyAdminAmount
                        ];
                    } else {
                        $cartdata[$sellerdetails['id']][]=[
                            'name'=>$product->getName(),
                            'number'=>$product->getId(),
                            'sku'=>$product->getSku(),
                            'qty'=>$item->getQty(),
                            'amt'=>$totalSellerAmount
                        ];
                    }
                    if ($totalDiscountAmount < 0) {
                        $cartdata[$sellerdetails['id']][]=[
                            'name'=>"Discount",
                            'number'=>"discount",
                            'sku'=>"discount",
                            'qty'=>1,
                            'amt'=>$totalDiscountAmount
                        ];
                    }
                } else {
                    $flag=true;
                    foreach ($cartdata as $key => $values) {
                        if ($key==$sellerdetails['id']) {
                            if ($key == 0) {
                                $adminTaxAmount = ($this->mpHelper->getConfigTaxManage()) ?
                                $adminTaxAmount += $sellerTaxAmount : $adminTaxAmount;
                                $cartdata[$key][]=[
                                    'name'=>$product->getName(),
                                    'number'=>$product->getId(),
                                    'sku'=>$product->getSku(),
                                    'qty'=>$item->getQty(),
                                    'amt'=>$onlyAdminAmount
                                ];
                            } else {
                                $cartdata[$key][]=[
                                    'name'=>$product->getName(),
                                    'number'=>$product->getId(),
                                    'sku'=>$product->getSku(),
                                    'qty'=>$item->getQty(),
                                    'amt'=>$totalSellerAmount
                                ];
                            }

                            if ($totalDiscountAmount < 0) {
                                $cartdata[$key][]=[
                                    'name'=>"Discount",
                                    'number'=>"discount",
                                    'sku'=>"discount",
                                    'qty'=>1,
                                    'amt'=>$totalDiscountAmount
                                ];
                            }
                            $flag=false;
                        }
                    }
                    if ($flag) {
                        if ($sellerdetails['id'] == 0) {
                            $adminTaxAmount = ($this->mpHelper->getConfigTaxManage()) ?
                            $adminTaxAmount += $sellerTaxAmount : $adminTaxAmount;
                            $cartdata[$sellerdetails['id']]=[
                                [
                                    'name'=>$product->getName(),
                                    'number'=>$product->getId(),
                                    'sku'=>$product->getSku(),
                                    'qty'=>$item->getQty(),
                                    'amt'=>$onlyAdminAmount
                                ]
                            ];
                        } else {
                            $cartdata[$sellerdetails['id']]=[
                                [
                                    'name'=>$product->getName(),
                                    'number'=>$product->getId(),
                                    'sku'=>$product->getSku(),
                                    'qty'=>$item->getQty(),
                                    'amt'=>$totalSellerAmount
                                ]
                            ];
                        }

                        if ($totalDiscountAmount < 0) {
                            $cartdata[$sellerdetails['id']][]=[
                                'name'=>"Discount",
                                'number'=>"discount",
                                'sku'=>"discount",
                                'qty'=>1,
                                'amt'=>$totalDiscountAmount
                            ];
                        }
                    }
                }
            }
            if ($commission>0) {
                $flag = true;
                foreach ($cartdata as $key => $values) {
                    if ($key==0) {
                        array_push(
                            $cartdata[0],
                            [
                                'name'=>'Fee',
                                'number'=>'comm',
                                'sku'=>"comm",
                                'qty'=>1,
                                'amt'=>$commission + $adminTaxAmount
                            ]
                        );
                        $flag=false;
                    }
                }
                if ($flag) {
                    $cartdata[0]=[
                        [
                            'name'=>'Fee',
                            'number'=>'comm',
                            'sku'=>"comm",
                            'qty'=>1,
                            'amt'=>$commission + $adminTaxAmount
                        ]
                    ];
                }
            } elseif (count($cartdata)==1 && isset($cartdata[0]) && $onlyAdminTaxAmount > 0) {
                array_push(
                    $cartdata[0],
                    [
                        'name'=>'Tax',
                        'number'=>'tax',
                        'sku'=>"comm",
                        'qty'=>1,
                        'amt'=>$onlyAdminTaxAmount
                    ]
                );
            }
            return $cartdata;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data getCartDetail : ".$e->getMessage());
            return [];
        }
    }

    public function updateCommissionData($commissionData)
    {
        try {
            $tempcoms = $commissionData['tempcoms'];
            $commissionDetail = $commissionData['commissionDetail'];
            if (!$tempcoms) {
                $commissionDetail = $this->mpPaymentHelper->getSellerDetail($commissionData['product_id']);

                if ($commissionDetail['id'] !== 0) {
                    $paypalid = $this->getSellerPaypalId($commissionDetail['id']);
                    if (!$paypalid) {
                        $commissionDetail['id'] = 0;
                        $commissionDetail['commission'] = 0;
                    }
                }

                if ($commissionDetail['id'] !== 0
                    && $commissionDetail['commission'] !== 0
                ) {
                    $tempcoms = round(
                        ($commissionData['row_total'] * $commissionDetail['commission']) / 100,
                        2
                    );
                }
            }
            return [
                'tempcoms' => $tempcoms,
                'commissionDetail' => $commissionDetail
            ];
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data updateCommissionData : ".$e->getMessage());
            return $commissionData;
        }
    }

    public function getSellerPaypalEmailByMerchantId($merchantId)
    {
        try {
            $collection = $this->sellerCollectionFactory->create()
                ->addFieldToFilter(
                    'paypal_merchant_id',
                    $merchantId
                );
            if ($collection->getSize()) {
                foreach ($collection as $value) {
                    return $value->getPaypalId();
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data getSellerPaypalEmailByMerchantId : ".$e->getMessage());
        }
    }

    public function getSellerTransactionDetails($additionalInfo)
    {
        try {
            $transactionDetails = [];
            $i = 0;

            while (isset($additionalInfo['PAYMENTINFO_'.$i.'_TRANSACTIONID'])) {
                if (isset($additionalInfo['PAYMENTINFO_'.$i.'_SELLERPAYPALACCOUNTID'])) {
                    $sellerEmail = $additionalInfo['PAYMENTINFO_'.$i.'_SELLERPAYPALACCOUNTID'];
                } elseif (isset($additionalInfo['PAYMENTINFO_'.$i.'_SECUREMERCHANTACCOUNTID'])) {
                    $sellerEmail = $this->getSellerPaypalEmailByMerchantId(
                        $additionalInfo['PAYMENTINFO_'.$i.'_SECUREMERCHANTACCOUNTID']
                    );
                }
                if (empty($sellerEmail)) {
                    $sellerEmail = $this->getConfigValue('merchant_id');
                }

                if ($sellerEmail) {
                    $transactionDetails[$sellerEmail] = $additionalInfo['PAYMENTINFO_'.$i.'_TRANSACTIONID'];
                }
                $i++;
            }

            return $transactionDetails;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data getSellerTransactionDetails : ".$e->getMessage());
            return [];
        }
    }

    public function sendRequestGetResponse($nvp)
    {
        try {
            $url = 'https://api-3t.'
                    . $this->getSandboxStatus()
                    . 'paypal.com/nvp';
            $result = $this->sendNvpRequest(
                $url,
                $nvp
            );
            parse_str($result, $output);

            return $output;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data sendRequestGetResponse : ".$e->getMessage());
        }
    }

    public function prepareFinalShipping($order)
    {
        try {
            $finalShipping = [];

            $shippingData = $this->mpPaymentHelper->getShippingData($order);
            $newvar = $shippingData['newvar'];
            $shipinf = $shippingData['shipinf'];
            $shipmeth = $shippingData['shipping_method'];
            
            if (!empty($shipinf)) {
                foreach ($shipinf as $key => $info) {
                    $sellerId = $info['seller'];
                    if ($sellerId!==0) {
                        $response = $this->getSellerPaypalId($info['seller']);
                        if (!$response) {
                            $sellerId = 0;
                        }
                    }
                    $finalShipping[$sellerId]['amount'] = $info['amount'];
                    $finalShipping[$sellerId]['method'] = $info['method'];
                }
            }

            if ($newvar == "" && empty($finalShipping)) {
                 $fee = 0;
	   	 
	   	 $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();        
		 $checkoutSession = $objectManager->get('\Magento\Checkout\Model\Session');
		 
		 if($checkoutSession->getQuote()->getId()){
		 	/*if($checkoutSession->getQuote()->isVirtual()){
				$fee= $checkoutSession->getQuote()->getBillingAddress()->getData('mc_paymentfee_amount');		 	
		 	}else{
		 	}*/
		 	$fee= $checkoutSession->getQuote()->getShippingAddress()->getData('mc_paymentfee_amount');
        	 }  
        	 if(!$fee){
        	 	$fee= $order->getShippingAddress()->getMcPaymentfeeAmount();
        	 }  
                $shipmethod = explode('_', $shipmeth, 2);
                $groups =  $order->getShippingAddress()
                    ->getGroupedAllShippingRates();

                foreach ($groups as $code => $rates) {
                    foreach ($rates as $rate) {
                        if ($rate->getCode()==$shipmeth
                            &&
                            $order->getShippingAddress()->getShippingMethod()==$shipmeth
                        ) {
                            $shipPrice = $order->getShippingAddress()->getBaseShippingAmount();
                            $taxAmount = $this->mpPaymentHelper->calculateTaxByPercent($shipPrice, $order);
                            $finalShipping[0]['amount'] = $shipPrice + $taxAmount + $fee ;
                           // $finalShipping[0]['amount'] = $shipPrice + $taxAmount ;
                            $finalShipping[0]['method'] = $shipmethod[1];
                            $finalShipping[1]['amount'] = $fee ;
                            $finalShipping[1]['method'] = 'mc_paymentfee_amount';
                            break;
                        }
                    }
                }
            }
           
            return $finalShipping;
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data prepareFinalShipping : ".$e->getMessage());
            return [];
        }
    }

    public function checkIsAdminValidUser()
    {
        try {
            $url = 'https://api-3t.'
                    . $this->getSandboxStatus()
                    . 'paypal.com/nvp';
            $nvp = [
                "USER" => $this->getConfigValue('api_username'),
                "PWD" => $this->getConfigValue('api_password'),
                "SIGNATURE" => $this->getConfigValue('api_signature'),
                "METHOD" => "GetPalDetails",
                "VERSION" => "72.0"
            ];
            $result = $this->sendNvpRequest(
                $url,
                $nvp
            );
            parse_str($result, $output);

            if (isset($output['ACK'])
                &&
                $output['ACK']=="Failure"
                &&
                isset($output['L_ERRORCODE0'])
                &&
                $output['L_ERRORCODE0']=='10002'
            ) {
                return false;
            } else {
                return true;
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data checkIsAdminValidUser : ".$e->getMessage());
            return false;
        }
    }

    public function formatPrice($price, $fromCurrency)
    {
        try {
            $rates = $this->storeManager->getStore()->getBaseCurrency()->getRate($fromCurrency);

            $price = $price / $rates;
            return $this->priceCurrency->round(
                $price
            );
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data formatPrice : ".$e->getMessage());
        }
    }

    public function getSellerId()
    {
        try {
            return $this->mpHelper->getCustomerId();
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data getSellerId : ".$e->getMessage());
        }
    }

    public function getAccessToken()
    {
        try {
            $url = "https://api.".$this->getSandboxStatus()."paypal.com/v1/oauth2/token";
            $params = [
                'grant_type' => "client_credentials"
            ];
            $options = [
                CURLOPT_USERPWD => $this->getConfigValue("client_id").":".$this->getConfigValue("client_secret")
            ];
            $response = $this->getResponseFromCurl($url, $params, [], $options);
            if (!empty($response['access_token'])) {
                return $response['access_token'];
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->logDataInLogger(
                __("getAccessToken Helper_Data : %1", $e->getMessage())
            );
            return false;
        }
    }

    public function createPayment($params, $accessToken)
    {
        $result = false;
        try {
            if ($accessToken) {
                $url = "https://api.".$this->getSandboxStatus()."paypal.com/v1/payments/payment";
                $headers = [
                    'Content-Type' => "application/json",
                    'Authorization' => "Bearer ".$accessToken,
                ];
                $response = $this->getResponseFromCurl($url, json_encode($params), $headers);
                $this->logInfo(
                    __("createPayment Helper_Data response : %1", json_encode($response))
                );
                if (!empty($response['id'])) {
                    $result = $response['id'];
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger(
                __("createPayment Helper_Data : %1", $e->getMessage())
            );
        }
        return $result;
    }

    public function logDataInLogger($data)
    {
        $this->logger->info($data);
    }
}
