<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rewards
 * @version   3.0.57
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\RewardsCheckout\Plugin\ThirdParty;

use Magento\Sales\Model\Order;
use Mirasvit\Rewards\Helper\Purchase;
use Mirasvit\Rewards\Helper\Serializer;

class BamboraOnlineAddRewardsPointsDiscount
{
    /**
     * @var Serializer
     */
    private $serializer;
    /**
     * @var Purchase
     */
    private $purchase;

    public function __construct(
        Purchase $purchase,
        Serializer $serializer
    ) {
        $this->purchase   = $purchase;
        $this->serializer = $serializer;
    }

    public function aroundCreateInvoice(\Bambora\Online\Model\Method\Epay\Payment $payment, \Closure $proceed, $order, $minorunits, $roundingMode)
    {
        $result = $proceed($order, $minorunits, $roundingMode);
        if ($result) {
            $invoice = $this->serializer->unserialize($result);

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $bamboraHelper = $objectManager->create('Bambora\Online\Helper\Data');

            $purchase = $this->purchase->getByOrder($order);
            $points = $purchase->getSpendAmount() * -1;
            $invoice['lines'][] = array(
                "id"          => "rewards_discount",
                "description" => __("Rewards discount"),
                "quantity"    => 1,
                "price"       => $bamboraHelper->convertPriceToMinorunits($points, $minorunits, $roundingMode),
            );

            $result = $this->serializer->serialize($invoice);
        }

        return $result;
    }
}
