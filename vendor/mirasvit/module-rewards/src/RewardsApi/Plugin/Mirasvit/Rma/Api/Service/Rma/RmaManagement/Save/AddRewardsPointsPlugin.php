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


namespace Mirasvit\RewardsApi\Plugin\Mirasvit\Rma\Api\Service\Rma\RmaManagement\Save;

use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Rewards\Helper\BehaviorRule;
use Mirasvit\Rewards\Model\Config;
use Mirasvit\Rma\Api\Data\RmaInterface;
use Mirasvit\Rma\Api\Service\Performer\PerformerInterface;
use Mirasvit\Rma\Api\Service\Rma\RmaManagement\SaveInterface;

/**
 * Add points for RMA creation
 *
 * @package Mirasvit\Rewards\Plugin
 */
class AddRewardsPointsPlugin
{
    /**
     * @var BehaviorRule
     */
    private $behaviorHelper;
    /**
     * @var StoreManagerInterface 
     */
    private $storeManager;

    public function __construct(
        BehaviorRule $behaviorHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->behaviorHelper = $behaviorHelper;
        $this->storeManager   = $storeManager;
    }

    /**
     * @param SaveInterface      $rmaSaveManagement
     * @param \callable          $proceed
     * @param PerformerInterface $performer
     * @param array              $data
     * @param array              $items
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundSaveRma(SaveInterface $rmaSaveManagement, $proceed, $performer, $data, $items)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);

        /** @var RmaInterface $rma */
        $rma = $proceed($performer, $data, $items);

        $store = $this->storeManager->getStore($rma->getStoreId());
        $this->behaviorHelper->processRule(Config::BEHAVIOR_TRIGGER_CREATED_RMA,
            $rma->getCustomerId(), $store->getWebsiteId(), $rma->getId(), ['rma' => $rma]);

        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);

        return $rma;
    }
}