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
namespace Webkul\MpStripe\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;

class StripeConfigProvider implements ConfigProviderInterface
{
    const DEFAULT_IMAGE = 'stripe-logo.png';
    /**
     * @var string[]
     */
    private $methodCode = PaymentMethod::METHOD_CODE;

    /**
     * $method.
     *
     * @var Magento\Payment\Helper\Data
     */
    private $method;

    /**
     * $helper.
     *
     * @var \Webkul\MpStripe\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    private $storeManager;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param PaymentHelper $paymentHelper
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Element\Template $template
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param Escaper $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        \Webkul\MpStripe\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\Template $template,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        Escaper $escaper
    ) {
    
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
        $this->helper = $helper;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->template = $template;
        $this->fileDriver = $fileDriver;
        $this->jsonHelper = $jsonHelper;
        $this->assetRepo = $assetRepo;
        $this->request = $request;
        $this->escaper = $escaper;
    }

    /**
     * getConfig function to return cofig data to payment renderer.
     *
     * @return []
     */
    public function getConfig()
    {
        if (!$this->helper->getIsActive()) {
            return [];
        }

        /*
         * [$mediaUrl base media folder to get image.
         *
         * @var [type]
         */
        $imageOnForm = $this->helper->getConfigValue('image_on_form');
        if ($imageOnForm) {
            $mediaImageUrl = $this->storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'mpstripe/config/';
            $mediaImageUrl .= $imageOnForm;
        } else {
            $mediaImageUrl = $this->template->getViewFileUrl('Webkul_MpStripe/images/mpstripe/config/stripe-logo.png');
            if (!$this->fileDriver->isExists($mediaImageUrl)) {
                $mediaImageUrl = "";
                $params = ['_secure' => $this->request->isSecure()];
                $mediaImageUrl =  $this->assetRepo
                ->getUrlWithParams('Webkul_MpStripe::images/mpstripe/config/'.self::DEFAULT_IMAGE, $params);
            }
        }

        /**
         * $config array to pass config data to payment renderer component.
         *
         * @var array
         */
        $config = [
            'payment' => [
                'mpstripe' => [
                    'title' => $this->helper->getConfigValue('title'),
                    'debug' => $this->helper->getConfigValue('debug'),
                    'api_key' => $this->helper->getConfigValue('api_key'),
                    'api_publish_key' => $this->helper->getConfigValue('api_publish_key'),
                    'name_on_form' => $this->helper->getConfigValue('name_on_form'),
                    'image_on_form' => $mediaImageUrl,
                    'min_order_total' => $this->helper->getConfigValue('min_order_total'),
                    'max_order_total' => $this->helper->getConfigValue('max_order_total'),
                    'sort_order' => $this->helper->getConfigValue('sort_order'),
                    'method' => $this->methodCode,
                    'saved_cards' => $this->getSavedCards(),
                    'currency' => $this->storeManager->getStore()->getCurrentCurrency()->getCode(),
                    'locale' => $this->helper->getLocaleForStripe(),
                    'getPaymentIntentUrl' => $this->urlBuilder->getUrl("mpstripe/payment/createintent"),
                    'cancelOrder' => $this->urlBuilder->getUrl("mpstripe/payment/cancelorder"),
                    'checkoutCart' => $this->urlBuilder->getUrl("checkout/cart")
                ],
            ],
        ];

        return $config;
    }

    /**
     * getSavedCards function to get customers cards json data.
     *
     * @return json
     */
    public function getSavedCards()
    {
        $cardsArray = [];
        $cards = $this->helper->getSavedCards();
        if ($cards) {
            foreach ($cards as $card) {
                if ($this->helper->customerExist($card->getStripeCustomerId())) {
                    array_push(
                        $cardsArray,
                        [
                            'exp_month' => $card->getExpiryMonth(),
                            'exp_year' => $card->getExpiryYear(),
                            'stripe_customer_id' => $card->getStripeCustomerId(),
                            'last4' => '****'.$card->getData('last4'),
                        ]
                    );
                }
            }
        }

        return $this->jsonHelper->jsonEncode($cardsArray);
    }
}
