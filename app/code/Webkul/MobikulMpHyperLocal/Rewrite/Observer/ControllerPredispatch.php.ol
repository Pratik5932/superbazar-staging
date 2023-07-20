<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulMpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MobikulMpHyperLocal\Rewrite\Observer;

use Webkul\MpHyperLocal\Observer\ControllerPredispatchObserver;
use Webkul\MpHyperLocal\Helper\Data;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\HTTP\Header;

class ControllerPredispatch extends ControllerPredispatchObserver
{
    /**
     * @param UrlInterface    $urlInterface
     * @param Data            $helper
     * @param Http            $request
     * @param Header          $httpHeader
     */
    public function __construct(
        UrlInterface $urlInterface,
        Data $helper,
        Http $request,
        Header $httpHeader
    ) {
        $this->urlInterface = $urlInterface;
        $this->_helper = $helper;
        $this->_request = $request;
        $this->httpHeader = $httpHeader;
        parent::__construct($urlInterface, $helper, $request, $httpHeader);
    }

    public function isRedirect($observer)
    {
        $whitelist = [
            'mobikulhttp',
            'mobikulmphttp',
            'mobikulmphl',
            'expressdelivery',
            'deliveryboy'
        ];
        // if ($this->_helper->getAddressOption() == 'redirect') {
        //     $address = $this->_helper->getSavedAddress();
        //     $currentUrl = $this->urlInterface->getCurrentUrl();
        //     $isRedirect = true;
        //     foreach ($whitelist as $uri) {
        //         if (strpos($currentUrl, $uri)) {
        //             $isRedirect = false;
        //         }
        //     }
        //     if ($isRedirect && strpos($currentUrl, 'mphyperlocal/address/index') === false) {
        //         if (!$address) {
        //             $addressUrl = $this->urlInterface->getUrl('mphyperlocal/address/index');
        //             $observer->getControllerAction()->getResponse()->setRedirect($addressUrl);
        //         }
        //     }
        // }
    }
}
