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

namespace Webkul\Mppaypalexpresscheckout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory as SaleslistCollection;
use Webkul\Marketplace\Model\ResourceModel\Orders\CollectionFactory as MpOrderCollection;
use Webkul\Mppaypalexpresscheckout\Model\ResourceModel\Mppaypalexpresscheckout\CollectionFactory;

/**
* Webkul Mppaypalexpresscheckout SalesOrderSuccessObserver Observer Model.
*/
class SalesOrderSuccessObserver implements ObserverInterface
{
    /**
    * @var \Magento\Sales\Api\OrderRepositoryInterface
    */
    private $orderRepository;

    /**
    * @var SaleslistCollection
    */
    private $salesListCollection;

    /**
    * @var \Webkul\Marketplace\Helper\Orders
    */
    private $mpHelperOrder;

    /**
    * @var MpOrderCollection
    */
    private $mpOrderCollection;

    /**
    * @var \Webkul\Mppaypalexpresscheckout\Helper\Data
    */
    private $helper;

    /**
    * @var CollectionFactory
    */
    private $collection;

    /**
    * @var \Webkul\Marketplace\Helper\Payment
    */
    private $mpPaymentHelper;

    /**
    * checkout_onepage_controller_success_action event handler.
    *
    * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
    * @param SaleslistCollection $salesListCollection
    * @param \Webkul\Marketplace\Helper\Orders $mpHelperOrder
    * @param MpOrderCollection $mpOrderCollection
    * @param \Webkul\Mppaypalexpresscheckout\Helper\Data $helper
    * @param CollectionFactory $collection
    * @param \Webkul\Marketplace\Helper\Payment $mpPaymentHelper
    */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        SaleslistCollection $salesListCollection,
        \Webkul\Marketplace\Helper\Orders $mpHelperOrder,
        MpOrderCollection $mpOrderCollection,
        \Webkul\Mppaypalexpresscheckout\Helper\Data $helper,
        CollectionFactory $collection,
        \Webkul\Marketplace\Helper\Payment $mpPaymentHelper
    ) {
        $this->orderRepository = $orderRepository;
        $this->salesListCollection = $salesListCollection;
        $this->mpHelperOrder = $mpHelperOrder;
        $this->mpOrderCollection = $mpOrderCollection;
        $this->helper = $helper;
        $this->collection = $collection;
        $this->mpPaymentHelper = $mpPaymentHelper;
    }

    /**
    * checkout_onepage_controller_success_action event handler.
    *
    * @param \Magento\Framework\Event\Observer $observer
    */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $orderIds = $observer->getOrderIds();
            $paymentCode = '';
            $transactionId = '';
            $sellersMerchantIds = [];

            foreach ($orderIds as $lastOrderId) {
                $order = $this->orderRepository->get($lastOrderId);
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $urlInterface =  $objectManager->get("Magento\Framework\UrlInterface");
                $orderPurchaseFactory =  $objectManager->get("Webkul\MobikulCore\Model\OrderPurchasePointFactory");
                $url = $urlInterface->getCurrentUrl();
                if (stripos($url, "mobikulhttp") === false && $order) {
                    $purchasePointColl = $orderPurchaseFactory->create()
                    ->getCollection()
                    ->addFieldToFilter("order_id", $order->getEntityId())
                    ->getFirstItem();

                    if ($purchasePointColl->getEntityId()) {
                        $purchasePoint = $orderPurchaseFactory->create()->load($purchasePointColl->getEntityId());
                        $purchasePoint->setIncrementId($order->getIncrementId());
                        $purchasePoint->setOrderId($order->getEntityId());
                        $purchasePoint->setPurchasePoint('web');
                        $purchasePoint->save();
                    } else {
                        $purchasePoint = $orderPurchaseFactory->create();
                        $purchasePoint->setIncrementId($order->getIncrementId());
                        $purchasePoint->setOrderId($order->getEntityId());
                        $purchasePoint->setPurchasePoint('web');
                        $purchasePoint->save();
                    }
                }

                if ($order->getPayment()) {
                    $paymentCode = $order->getPayment()->getMethod();
                    $transactionId = $order->getPayment()->getLastTransId();
                    $info = $order->getPayment()->getAdditionalInformation();
                }
                if (!empty($info)) {
                    for ($i=0; $i < 20; $i++) {
                        if (isset($info['PAYMENTINFO_'.$i.'_SECUREMERCHANTACCOUNTID'])) {
                            $sellersMerchantIds[] = $info['PAYMENTINFO_'.$i.'_SECUREMERCHANTACCOUNTID'];
                        } elseif (isset($info['raw_details_info']['PAYMENTINFO_'.$i.'_SECUREMERCHANTACCOUNTID'])) {
                            $sellersMerchantIds[] = $info['raw_details_info'][
                                'PAYMENTINFO_'.$i.'_SECUREMERCHANTACCOUNTID'
                            ];
                        } else {
                            break;
                        }
                    }
                }

                if ($paymentCode=="mppaypalexpresscheckout") {
                    $returnData = $this->mpPaymentHelper->getSellerOrderData($order->getId());

                    $data = $this->collection->create()
                    ->addFieldToFilter(
                        'paypal_merchant_id',
                        ['in' => $sellersMerchantIds]
                    )
                    ->addFieldToSelect('seller_id');
                    $activeSellers = $data->getColumnValues('seller_id');

                    $idsToCreateInvoice = $returnData['seller_amount_data'];
                    $flag = $returnData['flag'];
                    $sellerIds = $returnData['seller_ids_data'];

                    $this->mpHelperOrder->getCommssionCalculation($order);

                    $this->changeSellerPaidStatus($order, $activeSellers);

                    $this->generateInvoice(
                        $idsToCreateInvoice,
                        $activeSellers,
                        $order->getId(),
                        $paymentCode,
                        $transactionId
                    );

                    if ($transactionId && $transactionId!=="" && $transactionId!==0) {
                        $this->payToSellerMethod($order, $sellerIds, $flag, $transactionId);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Observer_SalesOrderSuccessObserver_execute Exception : ".$e->getMessage()
            );
        }
    }

    /**
    * changeSellerPaidStatus
    *
    * @param \Magento\Sales\Model\Order $order
    * @param array $activeSellers
    * @return void
    */
    private function changeSellerPaidStatus($order, $activeSellers)
    {
        try {
            $ordercollection = $this->salesListCollection->create()
            ->addFieldToFilter(
                'order_id',
                $order->getId()
            );
            foreach ($ordercollection as $item) {
                if (!$this->helper->getSellerPaypalId($item->getSellerId())
                || !in_array($item->getSellerId(), $activeSellers)
                ) {
                    $this->saveCpprostatus($item);
                    $this->mpPaymentHelper->revertSellerPayment(
                        $order,
                        $item->getSellerId()
                    );
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Observer_SalesOrderSuccessObserver_changeSellerPaidStatus Exception : ".$e->getMessage()
            );
        }
    }

    private function saveCpprostatus($item)
    {
        $item->setCpprostatus(0)->save();
    }

    /**
    * payToSellerMethod Pay to seller if action type is pay
    *
    * @param  array $sellerIds
    * @param  bool $flag
    * @param  int $trId
    * @return void
    */
    private function payToSellerMethod($order, $sellerIds, $flag, $trId)
    {
        try {
            if (!empty($sellerIds) && $flag == 1) {
                $data = $this->collection->create()
                ->addFieldToFilter(
                    'seller_id',
                    ['in' => $sellerIds]
                );
                if ($data->getSize()) {
                    foreach ($data as $paypaldetail) {
                        $paypalid = $paypaldetail->getPaypalId();
                        $enabledStatus = \Webkul\Mppaypalexpresscheckout\Model\Mppaypalexpresscheckout::STATUS_ENABLED;
                        if ($paypaldetail->getStatus() == $enabledStatus) {
                            $this->mpHelperOrder->paysellerpayment(
                                $order,
                                $paypaldetail->getSellerId(),
                                $trId
                            );
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Observer_SalesOrderSuccessObserver_payToSellerMethod Exception : ".$e->getMessage()
            );
        }
    }

    /**
    * generateInvoice
    *
    * @param array $idsToCreateInvoice
    * @param array $activeSellers
    * @param int $orderId
    * @param string $paymentCode
    * @param string $payKey
    * @return void
    *
    * @SuppressWarnings(PHPMD.CyclomaticComplexity)
    */
    private function generateInvoice(
        $idsToCreateInvoice,
        $activeSellers,
        $orderId,
        $paymentCode,
        $payKey
    ) {
        try {
            $order = $this->orderRepository->get($orderId);
            $sellerIdsToUnsetInvoiceIds = [];
            if (!empty($idsToCreateInvoice)) {
                $shipadminnew = 0;
                $idsToCreateInvoice[0] = 0;
                $shippingAmount = 0;
                $totalShippingAmount = 0;
                $activeSellers[] = 0;
                $adminShippingAmount = 0;

                foreach ($idsToCreateInvoice as $key => $value) {
                    $sellerId = $key;
                    if ($order->canUnhold()) {
                        $this->helper->logDataInLogger("Can not create invoice as order is in HOLD state");
                    } else {
                        $invoiceId = 0;
                        $codCharges = 0;
                        $shippingAmount = 0;
                        $marketplaceOrder = $this->mpOrderCollection->create()
                        ->addFieldToFilter('order_id', $orderId)
                        ->addFieldToFilter('seller_id', $sellerId);
                        foreach ($marketplaceOrder as $tracking) {
                            $shippingAmount = $tracking->getShippingCharges();
                            $codCharges = $this->mpPaymentHelper->getCodChargesIfApplied(
                                $paymentCode,
                                $tracking,
                                $codCharges
                            );
                            $invoiceId = $tracking->getInvoiceId();
                        }

                        if (!$invoiceId) {
                            $items = [];
                            $itemsarray = [];
                            $codCharges = 0;
                            $tax = 0;
                            $sellerCouponAmount = 0;

                            $collection = $this->salesListCollection->create()
                            ->addFieldToFilter('seller_id', $sellerId)
                            ->addFieldToFilter('order_id', $orderId);

                            foreach ($collection as $saleproduct) {
                                $codCharges = $this->mpPaymentHelper->getCodChargesIfApplied(
                                    $paymentCode,
                                    $saleproduct,
                                    $codCharges
                                );
                                $tax += $saleproduct->getTotalTax();
                                $sellerCouponAmount += $saleproduct->getAppliedCouponAmount();
                                array_push($items, $saleproduct['order_item_id']);
                            }

                            if ((int)$shippingAmount==0 && (int)$sellerId==0) {
                                $shipadminnew = $order->getBaseShippingAmount();

                                if ($totalShippingAmount < $shipadminnew) {
                                    $shippingAmount = $shipadminnew - $totalShippingAmount;
                                    $adminShippingAmount = $shippingAmount;
                                } else {
                                    $totalShippingAmount = 0;
                                }
                            } elseif (!$this->helper->getConfigValue('is_invoice_manage')
                            && !in_array($sellerId, $activeSellers) && $shippingAmount!==0
                            ) {
                                $shipadminnew = $order->getBaseShippingAmount();

                                if ($totalShippingAmount < $shipadminnew) {
                                    $adminShippingAmount += $shippingAmount;
                                }
                            }

                            $totalShippingAmount += $shippingAmount;
                            $itemsarray = $this->mpPaymentHelper->getItemQtys($order, $items);
                            $total = $itemsarray['subtotal'] + $shippingAmount + $codCharges + $tax;
                            /*invoice*/
                            if ((!empty($itemsarray) && $total)
                            && (($sellerId==0 && !empty($items)) || $sellerId!==0)
                            ) {
                                if (in_array($sellerId, $activeSellers)
                                || $this->helper->getConfigValue('is_invoice_manage')
                                ) {
                                    $this->mpPaymentHelper->createSellerOrderInvoice(
                                        $order,
                                        $itemsarray,
                                        $payKey,
                                        $paymentCode,
                                        $shippingAmount,
                                        $sellerId,
                                        $codCharges,
                                        $tax,
                                        $sellerCouponAmount
                                    );
                                } else {
                                    $sellerIdsToUnsetInvoiceIds[] = $sellerId;
                                }
                            }
                        }
                    }
                }
                $this->mpPaymentHelper->createShippingInvoice(
                    $order,
                    $payKey,
                    $adminShippingAmount
                );
            } else {
                $this->mpPaymentHelper->createOrderInvoice($order, $payKey);
            }
            if (!empty($sellerIdsToUnsetInvoiceIds)) {
                $this->unsetSellerOrderInvoiceId($sellerIdsToUnsetInvoiceIds, $order->getId());
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Observer_SalesOrderSuccessObserver_generateInvoice Exception : ".$e->getMessage()
            );
        }
    }

    /**
    * unsetSellerOrderInvoiceId removed invoice id from seller order data
    *
    * @param array $sellerIds
    * @param int $orderId
    * @return void
    */
    private function unsetSellerOrderInvoiceId($sellerIds, $orderId)
    {
        try {
            $sellerOrder = $this->mpOrderCollection->create()
            ->addFieldToFilter('seller_id', ['in' => $sellerIds])
            ->addFieldToFilter('order_id', $orderId);
            foreach ($sellerOrder as $info) {
                if ($info->getInvoiceId()) {
                    $this->saveCpprostatus($info);
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Observer_SalesOrderSuccessObserver_unsetSellerOrderInvoiceId Exception : ".$e->getMessage()
            );
        }
    }
}
