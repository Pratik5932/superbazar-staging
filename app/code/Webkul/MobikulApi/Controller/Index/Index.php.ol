<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MobikulApi\Controller\Index;

/**
 * Mppaypalexpresscheckout Index Index Controller.
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Webkul\Mppaypalexpresscheckout\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Directory\Model\Region
     */
    private $region;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $countryFactory;

    private $requiredStateforCountry = ['AR', 'BR', 'CA', 'CN', 'ID', 'IN', 'JP', 'MX', 'TH', 'US'];

    /**
     * @param \Magento\Framework\App\Action\Context       $context
     * @param \Webkul\Mppaypalexpresscheckout\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session             $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface  $storeManager
     * @param \Magento\Directory\Model\Region             $region
     * @param \Magento\Directory\Model\CountryFactory     $countryFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Webkul\Mppaypalexpresscheckout\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Region $region,
        \Magento\Directory\Model\CountryFactory $countryFactory
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->region = $region;
        $this->countryFactory = $countryFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $quoteFactory = $objectManager->create(\Magento\Quote\Model\QuoteFactory::class);
        try {
            $nvps = [];
            $finalShipping = [];
            $this->checkoutSession->start();
            $quoteId = $this->checkoutSession->getMobikulPaypalQuoteId();
            $order = $quoteFactory->create()->load($quoteId);
            $isVirtual = $order->getIsVirtual();
            $storeId = $this->storeManager->getStore()->getStoreId();

            if ($isVirtual!==1) {
                $finalShipping = $this->helper->prepareFinalShipping($order);
            }
            // print_r($finalShipping);
            $this->_eventManager->dispatch(
                'mp_advance_commission_rule',
                ['order' => $order]
            );
            $params = $this->getRequest()->getParams();

            if (!isset($params['token'])) {
                $cartdata = $this->helper->getCartDetail($order);
                $i=0;

                $nvps = [
                    "USER" => $this->helper->getConfigValue('api_username'),
                    "PWD" => $this->helper->getConfigValue('api_password'),
                    "SIGNATURE" => $this->helper->getConfigValue('api_signature'),
                    "METHOD" => "SetExpressCheckout",
                    "VERSION" => "72.0",
                    "RETURNURL" => $this->_url->getUrl('mppaypalexpresscheckout/index/return'),
                    "CANCELURL" => $this->_url->getUrl('mppaypalexpresscheckout/index/cancel'),
                    "_CURRENCYCODE" => $order->getBaseCurrencyCode()
                ];
                $rest = [
                    "intent" => "sale",
                    "payer" => [
                        "payment_method" => "paypal",
                    ],
                    "redirect_urls" => [
                        "return_url" => $this->_url->getUrl('mppaypalexpresscheckout/index/return'),
                        "cancel_url" => $this->_url->getUrl('mppaypalexpresscheckout/index/cancel'),
                    ]
                ];

                foreach ($cartdata as $key => $values) {
                    $itemc = 0;
                    $price = 0;
                    $rest["transactions"][$i] = [
                        'reference_id' => $key,
                        "amount" => [
                            "currency" => $order->getBaseCurrencyCode(),
                            "total" => 0,
                        ],
                        "payment_options" => [
                            "allowed_payment_method" => "INSTANT_FUNDING_SOURCE"
                        ],
                    ];
                    foreach ($values as $info) {
                        $nvps["L_PAYMENTREQUEST_".$i."_ITEMCATEGORY".$itemc] = "physical";
                        $nvps["L_PAYMENTREQUEST_".$i."_NAME".$itemc] = $info['name'];
                        $nvps["L_PAYMENTREQUEST_".$i."_NUMBER".$itemc] = $info['number'];
                        $nvps["L_PAYMENTREQUEST_".$i."_QTY".$itemc] = $info['qty'];
                        $nvps["L_PAYMENTREQUEST_".$i."_AMT".$itemc] = round($info['amt']/$info['qty'], 2);

                        $rest["transactions"][$i]["item_list"]["items"][] = [
                            "sku" => $info['number'],
                            "name" => $info['name'],
                            "description" => $info['name'],
                            "quantity" => $info['qty'],
                            "price" => round($info['amt']/$info['qty'], 2),
                            "currency" => $order->getBaseCurrencyCode(),
                        ];

                        $price += floatval(
                            round($info['amt']/$info['qty'], 2) * $info['qty']
                        );
                        $itemc++;
                    }
                    $adminship = true;
                    if (isset($finalShipping[$key])
                        && !empty($finalShipping[$key])
                        && $finalShipping[$key]['amount']>0
                    ) {
                        $adminship = false;
                        $qty=1;
                        $nvps["L_PAYMENTREQUEST_".$i."_ITEMCATEGORY".$itemc] = "physical";
                        $nvps["L_PAYMENTREQUEST_".$i."_NAME".$itemc] = 'shipping';
                        $nvps["L_PAYMENTREQUEST_".$i."_NUMBER".$itemc] = $finalShipping[$key]['method'];
                        $nvps["L_PAYMENTREQUEST_".$i."_QTY".$itemc] = $qty;
                        $nvps["L_PAYMENTREQUEST_".$i."_AMT".$itemc] = round($finalShipping[$key]['amount'], 2);

                        $rest["transactions"][$i]["item_list"]["items"][] = [
                            "sku" => $finalShipping[$key]['method'],
                            "name" => 'shipping',
                            "description" => $finalShipping[$key]['method'],
                            "quantity" => $qty,
                            "price" => round($finalShipping[$key]['amount'], 2),
                            "currency" => $order->getBaseCurrencyCode(),
                        ];

                        $price += floatval(
                            round($finalShipping[$key]['amount'], 2)
                        );
                    }
                    if ($price==0) {
                        continue;
                    }
                    $paypalid = $this->helper->getSellerPaypalEmail($key);
                    
                    $nvps["PAYMENTREQUEST_".$i."_CURRENCYCODE"] = $order->getBaseCurrencyCode();
                    $nvps["PAYMENTREQUEST_".$i."_AMT"] = $price;
                    $nvps["PAYMENTREQUEST_".$i."_SELLERPAYPALACCOUNTID"] = $paypalid;
                    $nvps["PAYMENTREQUEST_".$i."_ITEMAMT"] = $price;
                    $nvps["PAYMENTREQUEST_".$i."_PAYMENTACTION"] = "Sale";
                    $nvps["PAYMENTREQUEST_".$i."_PAYMENTREQUESTID"] = 'CAR6544-PAYMENT'.$i;
                    
                    $rest["transactions"][$i]['amount']['total'] += $price;
                    $rest["transactions"][$i]['payee'] = [
                        "email" => $paypalid,
                    ];
                    $i++;
                }

                if  ($adminship) {
                    $key = 0;
                    $price = 0;
                    $itemc = 0;
                    if (isset($finalShipping[$key])
                        && !empty($finalShipping[$key])
                        && $finalShipping[$key]['amount']>0
                    ) {
                        $rest["transactions"][$i] = [
                            'reference_id' => $key,
                            "amount" => [
                                "currency" => $order->getBaseCurrencyCode(),
                                "total" => 0,
                            ],
                            "payment_options" => [
                                "allowed_payment_method" => "INSTANT_FUNDING_SOURCE"
                            ],
                        ];

                        $qty=1;
                        $nvps["L_PAYMENTREQUEST_".$i."_ITEMCATEGORY".$itemc] = "physical";
                        $nvps["L_PAYMENTREQUEST_".$i."_NAME".$itemc] = 'shipping';
                        $nvps["L_PAYMENTREQUEST_".$i."_NUMBER".$itemc] = $finalShipping[$key]['method'];
                        $nvps["L_PAYMENTREQUEST_".$i."_QTY".$itemc] = $qty;
                        $nvps["L_PAYMENTREQUEST_".$i."_AMT".$itemc] = round($finalShipping[$key]['amount'], 2);

                        $rest["transactions"][$i]["item_list"]["items"][] = [
                            "sku" => $finalShipping[$key]['method'],
                            "name" => 'shipping',
                            "description" => $finalShipping[$key]['method'],
                            "quantity" => $qty,
                            "price" => round($finalShipping[$key]['amount'], 2),
                            "currency" => $order->getBaseCurrencyCode(),
                        ];

                        $price += floatval(
                            round($finalShipping[$key]['amount'], 2)
                        );
                    }
                    
                    if ($price != 0) {
                        $paypalid = $this->helper->getSellerPaypalEmail($key);
                        
                        $nvps["PAYMENTREQUEST_".$i."_CURRENCYCODE"] = $order->getBaseCurrencyCode();
                        $nvps["PAYMENTREQUEST_".$i."_AMT"] = $price;
                        $nvps["PAYMENTREQUEST_".$i."_SELLERPAYPALACCOUNTID"] = $paypalid;
                        $nvps["PAYMENTREQUEST_".$i."_ITEMAMT"] = $price;
                        $nvps["PAYMENTREQUEST_".$i."_PAYMENTACTION"] = "Sale";
                        $nvps["PAYMENTREQUEST_".$i."_PAYMENTREQUESTID"] = 'CAR6544-PAYMENT'.$i;
                        
                        $rest["transactions"][$i]['amount']['total'] += $price;
                        $rest["transactions"][$i]['payee'] = [
                            "email" => $paypalid,
                        ];
                    }
                }

                $cartaddress = $order->getShippingAddress();

                if ($cartaddress->getData() && $isVirtual!==1) {
                    if ($cartaddress->getRegionId()) {
                        $regionCode = $this->region->load($cartaddress->getRegionId())->getCode();
                    } else {
                        $regionCode = $cartaddress->getRegion();
                    }
                    $regionCode = ($cartaddress->getRegionId()) ?
                    $this->region->load($cartaddress->getRegionId())->getCode() :
                    $cartaddress->getRegion();
                    $nvps["PAYMENTREQUEST_0_SHIPTONAME"] = $order->getCustomerFirstname()
                                . " " . $order->getCustomerLastname();
                    $nvps["PAYMENTREQUEST_0_SHIPTOSTREET"] = implode("", $cartaddress->getStreet());
                    $nvps["PAYMENTREQUEST_0_SHIPTOCITY"] = $cartaddress->getCity();

                    if (!empty($rest["transactions"][0]["item_list"])) {
                        $rest["transactions"][0]["item_list"]['shipping_address'] = [
                            "line1" => implode("", $cartaddress->getStreet()),
                            "city" => $cartaddress->getCity(),
                            "country_code" => $cartaddress->getCountryId(),
                            "postal_code" => $cartaddress->getPostcode(),
                            "phone" => $cartaddress->getTelephone(),
                            "recipient_name" => $order->getCustomerFirstname()
                                                    . " " . $order->getCustomerLastname()
                        ];

                        $rest["transactions"][0]["item_list"]['shipping_method'] = $cartaddress->getShippingMethod();
                    }

                    if (in_array($cartaddress->getCountryId(), $this->requiredStateforCountry)
                        && ($regionCode=="" || !$regionCode)
                    ) {
                        $country = $this->countryFactory->create()->loadByCode($cartaddress->getCountryId());
                        $this->messageManager->addError(__('State field is required for country "%1" ', $country->getName()));
                        return $this->resultRedirectFactory->create()->setPath(
                            'checkout/cart'
                        );
                    } elseif ($regionCode && $regionCode!=="") {
                        $nvps["PAYMENTREQUEST_0_SHIPTOSTATE"] = $regionCode;
                        if (!empty($rest["transactions"][0]["item_list"])) {
                            $rest["transactions"][0]["item_list"]['shipping_address']['state'] = $regionCode;
                        }
                    }

                    $nvps["PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE"] = $cartaddress->getCountryId();
                    $nvps["PAYMENTREQUEST_0_SHIPTOZIP"] = $cartaddress->getPostcode();
                    $nvps["PAYMENTREQUEST_0_SHIPTOPHONENUM"] = $cartaddress->getTelephone();
                }

                $nvps["SOLUTIONTYPE"] = "Sole";
                $nvps["localecode"] = "US";
                $nvps["hdrbordercolor"] = "ffffff";
                $nvps["hdrbackcolor"] = "cacdb2";
                $nvps["channeltype"] = "Merchant";
                $nvps["reqconfirmshipping"] = "0";
                $nvps["NOSHIPPING"] = "1";
                $nvps["ADDROVERRIDE"] = "0";
                if ($isVirtual==1) {
                    $nvps["NOSHIPPING"] = "1";
                    $nvps["ADDROVERRIDE"] = "0";
                }
                $this->helper->logDataInLogger("SetExpressCheckout Request : ".json_encode($nvps));

                $getEC = $this->getToken($nvps);
                $this->helper->logDataInLogger("SetExpressCheckout Response : ".json_encode($getEC));
                if (isset($getEC['ACK']) && $getEC['ACK']=="Success") {
                    $paypalurl ="https://www."
                        . $this->helper->getSandboxStatus()
                        . "paypal.com/cgi-bin/webscr?cmd=_express-checkout&token="
                        . $getEC["TOKEN"];
// echo $paypalurl;die;
                    $this->_response->setHeader("Location", $paypalurl)->sendHeaders();
                } elseif (isset($getEC['ACK']) && $getEC['ACK']=="Failure") {
                    if (isset($getEC['L_ERRORCODE0'])) {
                        for ($i=0; $i < count($getEC)-5; $i++) {
                            if (isset($getEC['L_ERRORCODE'.$i]) && isset($getEC['L_LONGMESSAGE'.$i])) {
                                $this->messageManager->addError(__($getEC['L_LONGMESSAGE'.$i]));
                            }
                        }
                    }
                    return $this->resultRedirectFactory->create()->setPath(
                        'checkout/cart'
                    );
                } else {
                    $this->messageManager->addError(__('Something went wrong, please try again'));
                    return $this->resultRedirectFactory->create()->setPath(
                        'checkout/cart'
                    );
                }
            } else {
                $this->messageManager->addError(__('Something went wrong, please try again'));
                return $this->resultRedirectFactory->create()->setPath(
                    'checkout/cart'
                );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->helper->logDataInLogger("LocalizedException Controller_index_index execute : ".$e->getMessage());
            $this->messageManager->addError(__($e->getMessage()));
            return $this->resultRedirectFactory->create()->setPath(
                'checkout/cart'
            );
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Exception Controller_index_index execute : ".$e->getMessage());
            $this->messageManager->addError(__($e->getMessage()));
            return $this->resultRedirectFactory->create()->setPath(
                'checkout/cart'
            );
        }
    }

    private function getToken($nvp)
    {
        try {
            $url = 'https://api-3t.'
                    . $this->helper->getSandboxStatus()
                    . 'paypal.com/nvp';
            $result = $this->helper->sendNvpRequest(
                $url,
                $nvp
            );
            parse_str($result, $output);
            return $output;
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Exception Controller_index_index getToken : ".$e->getMessage());
        }
    }
}
