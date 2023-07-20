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

use Magento\Checkout\Model\Session;
use Mirasvit\Rewards\Helper\Purchase;

class CustomwebDocDataCwResetPurchaseOrderPlugin
{
    private $checkoutSession;

    private $purchaseHelper;

    public function __construct(
        Session $checkoutSession,
        Purchase $purchaseHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->purchaseHelper  = $purchaseHelper;
    }

    public function aroundExecute(\Customweb\DocDataCw\Controller\Checkout\Failure $class, \Closure $proceed)
    {
        $transaction = $this->getTransaction($class->getRequest()->getParam('cstrxid'));
        $result = $proceed();
        if ($transaction) {
            $purchase = $this->purchaseHelper->getByQuote($transaction->getOrder()->getQuoteId(), false);
            if ($purchase) {
                $purchase->setOrderId(null)
                    ->save();
            }
        }

        return $result;
    }
    /**
     * @param int $transactionId
     * @return \Customweb\DocDataCw\Model\Authorization\Transaction
     * @throws \Exception
     */
    private function getTransaction($transactionId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $transactionFactory = $objectManager->create('Customweb\DocDataCw\Model\Authorization\TransactionFactory');
        $transaction = $transactionFactory->create()->load($transactionId);
        if (!$transaction->getId()) {
            return false;
        }
        return $transaction;
    }
}
