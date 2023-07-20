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



namespace Mirasvit\Rewards\Service;

class RoundService
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;
    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param float $number
     * @param bool   $roundUp
     *
     * @return int
     */
    public function round($number, $roundUp)
    {
        $number = $this->faonniRound($number);

        if (!is_int($number)) {
            $number = $roundUp ? ceil($number) : floor($number);
        }

        return $number;
    }

    /**
     * @param float $number
     *
     * @return int|float
     */
    public function faonniRound($number)
    {
        if (!$this->isFaonniRoundEnabled()) {
            return $number;
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $math = $objectManager->create('Faonni\Price\Model\Math');

        return $math->round($number);
    }

    /**
     * @return bool
     */
    public function isFaonniRoundEnabled()
    {
        if ($this->moduleManager->isEnabled('Faonni_Price')) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $helper = $objectManager->create('Faonni\Price\Helper\Data');
            if ($helper->isEnabled() &&
                $helper->isRoundingDiscount()
            ) {
                return true;
            }
        }

        return false;
    }
}