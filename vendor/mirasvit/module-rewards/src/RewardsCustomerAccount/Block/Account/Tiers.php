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



namespace Mirasvit\RewardsCustomerAccount\Block\Account;

use Mirasvit\Rewards\Api\Data\TierInterface;
use Mirasvit\Rewards\Helper\Balance as BalanceHelper;
use Mirasvit\Rewards\Helper\Data as DataHelper;
use Mirasvit\Rewards\Helper\Tier\Order as tierOrderHelper;
use Mirasvit\Rewards\Model\Config;
use Mirasvit\Rewards\Model\Tier\Backend\FileProcessor;

/**
 * Class Tiers
 *
 * Customer account Tiers block. Displays customer progress.
 *
 * @package Mirasvit\RewardsCustomerAccount\Block\Account
 */
class Tiers extends \Magento\Framework\View\Element\Template
{
    /**
     * @var BalanceHelper
     */
    private $balanceHelper;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var DataHelper
     */
    private $dataHelper;
    /**
     * @var FileProcessor
     */
    private $fileProcessor;
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    private $filterProvider;
    /**
     * @var TierInterface
     */
    private $nextTier;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;
    /**
     * @var tierOrderHelper
     */
    private $tierOrderHelper;
    /**
     * @var \Mirasvit\Rewards\Api\Service\Customer\TierInterface
     */
    private $tierService;
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $context;

    public function __construct(
        \Mirasvit\Rewards\Api\Service\Customer\TierInterface $tierService,
        BalanceHelper $balanceHelper,
        DataHelper $dataHelper,
        tierOrderHelper $tierOrderHelper,
        Config $config,
        FileProcessor $fileProcessor,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->tierService = $tierService;
        $this->balanceHelper = $balanceHelper;
        $this->dataHelper = $dataHelper;
        $this->tierOrderHelper = $tierOrderHelper;
        $this->config = $config;
        $this->fileProcessor = $fileProcessor;
        $this->priceCurrency = $priceCurrency;
        $this->filterProvider = $filterProvider;
        $this->customerSession = $customerSession;
        $this->context = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return \Mirasvit\Rewards\Model\ResourceModel\Tier\Collection|TierInterface[]
     */
    public function getTiers()
    {
        return $this->tierService->getCustomerTiers($this->getCustomer()->getId());
    }

    /**
     * @return int
     */
    public function getBalance()
    {
        $days = $this->config->getTierCalculationPeriod();
        $website = $this->getCustomer()->getStore()->getWebsite();
        if ($this->config->getTierCalcType($website) == TierInterface::TYPE_ORDER) {
            $points = $this->tierOrderHelper->getSumForLastDays($this->getCustomer(), $days);
        } else {
            $points = $this->balanceHelper->getPointsForLastDays($this->getCustomer(), $days);
        }

        return $points;
    }

    /**
     * @return \Mirasvit\Rewards\Api\Data\TierInterface|false
     */
    public function getCurrentTier()
    {
        return $this->tierService->getCustomerTier($this->getCustomer()->getId());
    }

    /**
     * @return string
     */
    public function getCurrentTierName()
    {
        return $this->getCurrentTier()->getName();
    }

    /**
     * @return string
     */
    public function getNextTierName()
    {
        $name = '';
        $nextTier = $this->getNextTier();
        if ($nextTier) {
            $name = $nextTier->getName();
        }
        return $name;
    }

    /**
     * @return \Mirasvit\Rewards\Api\Data\TierInterface
     */
    public function getNextTier()
    {
        if (!$this->nextTier) {
            $this->nextTier = $this->tierService->getNextTier($this->getCustomer()->getId());
        }

        return $this->nextTier;
    }

    /**
     * @return int
     */
    public function getRemainingPoints()
    {
        $tier = $this->getNextTier();
        if (!$tier) {
            return 0;
        }

        return $this->tierService->getRemainingPoints($tier, $this->getCustomer()->getId());
    }

    /**
     * @param int $points
     * @return string
     */
    public function formatPoints($points)
    {
        return $this->dataHelper->formatPoints($points);
    }

    /**
     * @param int  $points
     * @param bool $format
     * @return string
     */
    public function displayPoints($points, $format = false)
    {
        $website = $this->getCustomer()->getStore()->getWebsite();
        switch ($this->config->getTierCalcType($website)) {
            case TierInterface::TYPE_ORDER:
                $points = $this->priceCurrency->convertAndFormat($points, false, 0);
                break;
            case TierInterface::TYPE_POINT:
            default:
                if ($format) {
                    $points = $this->formatPoints($points);
                }
        }

        return $points;
    }

    /**
     * @return boolean
     */
    public function isTierTypePoints ()
    {
        $website = $this->getCustomer()->getStore()->getWebsite();
        $tierCalcType = $this->config->getTierCalcType($website);

        return $tierCalcType == TierInterface::TYPE_POINT;
    }

    public function toHtml()
    {
        $html = '';
        $tiers = $this->tierService->getCustomerTiers($this->getCustomer()->getId());
        if (count($tiers) > 1 && $this->getCurrentTier()) {
            $html = parent::toHtml();
        }

        return $html;
    }

    /**
     * @param \Mirasvit\Rewards\Api\Data\TierInterface $tier
     * @return string
     */
    public function getTierDescription($tier)
    {
        if ($tier->getDescription()) {
            return $this->escapeHtml($this->filterProvider->getPageFilter()->filter((string)$tier->getDescription()));
        } else {
            return '';
        }
    }

    /**
     * @param \Mirasvit\Rewards\Api\Data\TierInterface $tier
     * @return string
     */
    public function getTierLogoUrl($tier)
    {
        return $this->fileProcessor->getLogoMediaUrl($tier->getTierLogo());
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    protected function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }
}
