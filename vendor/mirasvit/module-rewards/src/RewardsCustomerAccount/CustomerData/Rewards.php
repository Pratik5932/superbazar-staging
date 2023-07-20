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



namespace Mirasvit\RewardsCustomerAccount\CustomerData;

use Mirasvit\Rewards\Service\MenuLink;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Mirasvit\Rewards\Helper\Balance;
use Magento\Customer\Model\Session;
use Mirasvit\Rewards\Helper\Data as DataHelper;
use Mirasvit\Rewards\Model\Config;

/**
 * Class Rewards
 * Added formated amount of customer points to js customer config object
 * @package Mirasvit\Rewards\CustomerData
 */
class Rewards implements SectionSourceInterface
{
    protected $menuLink;

    private   $balance;

    private   $customerSession;

    private   $dataHelper;

    private   $config;

    public function __construct(
        MenuLink $menuLink,
        Balance $balance,
        Session $customerSession,
        DataHelper $dataHelper,
        Config $config
    ) {
        $this->menuLink        = $menuLink;
        $this->balance         = $balance;
        $this->customerSession = $customerSession;
        $this->dataHelper      = $dataHelper;
        $this->config          = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $amount = 0;
        if ($balance = $this->getBalance()) {
            $amount = $this->dataHelper->formatPointsWithCutUnitName($balance);
        }

        return [
            'amount'        => $this->dataHelper->getLogoHtml() . $amount,
            'isVisible'     => (bool)$this->config->getDisplayOptionsIsShowPointsOnFrontend(),
            'isVisibleMenu' => (bool)$this->menuLink->isShowMenuForCurrentCustomer($this->customerSession->getCustomer()),
        ];
    }

    /**
     * @return int
     */
    public function getBalance()
    {
        return $this->balance->getBalancePoints($this->customerSession->getCustomer());
    }
}
