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



namespace Mirasvit\RewardsCheckout\Plugin;

use Mirasvit\Rewards\Helper\Purchase;
use Magento\Framework\Module\Manager;
use Magento\Framework\App\RequestInterface;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\App\State;

class RecollectEmptyQuotePurchase
{
    private $rewardsPurchase;

    private $moduleManager;

    private $cartHelper;

    private $request;

    private $state;

    public function __construct(
        Purchase $rewardsPurchase,
        Cart $cartHelper,
        Manager $moduleManager,
        RequestInterface $request,
        State $state
    ) {
        $this->rewardsPurchase = $rewardsPurchase;
        $this->moduleManager   = $moduleManager;
        $this->cartHelper      = $cartHelper;
        $this->request         = $request;
        $this->state           = $state;
    }

    /**
     * @param \Magento\Checkout\Model\Cart $subject
     * @param object $result
     *
     * @return object $result
     */
    public function afterRemoveItem(\Magento\Checkout\Model\Cart $subject, $result)
    {
        $purchase = $this->rewardsPurchase->getByQuote($subject->getQuote()->getId());

        if (!$purchase) {
            return $result;
        }

        if (!count($subject->getQuote()->getAllVisibleItems())) {
            $purchase->setSpendPoints(0)
                ->setBaseSpendAmount(0)
                ->setSpendAmount(0)
                ->setSpendMinPoints(0)
                ->setSpendMaxPoints(0)
                ->setEarnPoints(0)
                ->save();
        }

        return $result;
    }
}
