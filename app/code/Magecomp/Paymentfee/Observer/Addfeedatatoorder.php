<?php
namespace Magecomp\Paymentfee\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Api\Data\OrderExtension;
class Addfeedatatoorder implements ObserverInterface
{
    private $feeDataFields = [
        'mc_paymentfee_amount',
        'base_mc_paymentfee_amount',
        'mc_paymentfee_tax_amount',
        'base_mc_paymentfee_tax_amount'
    ];

    private $orderExtension;

    public function __construct(
        OrderExtension $orderExtension
    ) {
        $this->orderExtension = $orderExtension;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->orderExtension;
        }
        foreach ($this->feeDataFields as $key) {
            $data = $order->getData($key) ? $order->getData($key) : '0.0000';
            $extensionAttributes->setData($key, $data);
        }
         $order->setExtensionAttributes($extensionAttributes);
    }
}
