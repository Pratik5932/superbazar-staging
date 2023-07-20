<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mobikul
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Controller\Checkout;

use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Webkul\MobikulCore\Helper\Data as HelperData;

class Paypalredirect extends Action
{
    protected $helper;
    protected $emulate;
    protected $orderFactory;
    protected $checkoutSession;

    public function __construct(
        Context $context,
        Emulation $emulate,
        HelperData $helper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->helper = $helper;
        $this->emulate = $emulate;
        $this->orderFactory = $orderFactory;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resultRedirectFactory = $objectManager->create(\Magento\Framework\Controller\Result\RedirectFactory::class);
        $returnArray = [];
        try {
            $wholeData = $this->getRequest()->getParams();
            
            if ($wholeData) {
                $storeId = $wholeData["storeId"]     ?? 1;
                $quoteId = $wholeData["quoteId"] ?? 0;
                $environment  = $this->emulate->startEnvironmentEmulation($storeId);
                if ($quoteId) {
                    $this->checkoutSession->setMobikulPaypalQuoteId($quoteId);
                    return $resultRedirectFactory->create()->setPath(
                        'mobikulhttp/index/index'
                    );
                } else {
                    $returnArray['message'] = __("Invalid Request");;
                    $returnArray['success'] = false;
                    return $this->getResponse()->setBody(__("Invalid Request"));
                }
                $this->emulate->stopEnvironmentEmulation($environment);
                $this->helper->log($returnArray, "logResponse", $wholeData);
            } else {
                $returnArray["responseCode"] = 0;
                $returnArray["message"]      = __("Invalid Request");
                $this->helper->log($returnArray, "logResponse", $wholeData);
            }
        } catch (\Exception $e) {
            $returnArray["message"] = $e->getMessage();
            $this->helper->printLog($returnArray, 1);
            return $this->getResponse()->setBody($e->getMessage());
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray["message"] = $e->getMessage();
            $this->helper->printLog($returnArray, 1);
            return $this->getResponse()->setBody($e->getMessage());
        }
    }
}
    