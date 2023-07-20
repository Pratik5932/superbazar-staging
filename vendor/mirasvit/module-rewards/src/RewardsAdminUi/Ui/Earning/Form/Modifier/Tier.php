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


namespace Mirasvit\RewardsAdminUi\Ui\Earning\Form\Modifier;

use Mirasvit\Rewards\Api\Repository\TierRepositoryInterface;
use Mirasvit\Rewards\Helper\Tier\Option as HelperTier;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Ui\Component\Form;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Tier extends AbstractModifier
{
    const SORT_ORDER = 20;

    /**
     * @var array
     */
    protected $tierList = [];

    protected $earningStyleOption;
    private $helperTier;
    private $tierRepository;
    private $registry;
    private $storeManager;
    private $websiteRepository;
    private $groupRepository;
    private $storeRepository;

    public function __construct(
        OptionSourceInterface $earningStyleOption,
        HelperTier $helperTier,
        TierRepositoryInterface $tierRepository,
        Registry $registry,
        StoreManagerInterface $storeManager,
        WebsiteRepositoryInterface $websiteRepository,
        GroupRepositoryInterface $groupRepository,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->earningStyleOption = $earningStyleOption;
        $this->helperTier = $helperTier;
        $this->tierRepository = $tierRepository;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->websiteRepository = $websiteRepository;
        $this->groupRepository = $groupRepository;
        $this->storeRepository = $storeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        /** @var \Mirasvit\Rewards\Model\Earning\Rule $rule */
        $rule = $this->registry->registry('current_earning_rule');

        if (!$rule->getId()) {
            return $data;
        }
        $tiers = $rule->getTiersSerialized();
        if ($tiers) {
            foreach ($this->getTierList() as $tier) {
                $tierId = $tier->getId();
                if (isset($tiers[$tierId])) {
                    $data[$rule->getId()]['tier'][$tierId] = $this->getTierData($tiers[$tierId]);
                } else {
                    $data[$rule->getId()]['tier'][$tierId] = $this->getDefaultTierData();
                }
            }
        }

        return $data;
    }

    /**
     * @param array $tier
     * @return array
     */
    abstract protected function getTierData($tier);

    /**
     * @return array
     */
    abstract protected function getDefaultTierData();

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        /** @var \Mirasvit\Rewards\Model\Earning\Rule $rule */
        $rule = $this->registry->registry('current_earning_rule');

        if (!$rule->getId()) {
            return $meta;
        }
        $meta = array_replace_recursive(
            $meta,
            [
                'tiers' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'additionalClasses' => 'admin__fieldset-actions',
                                'label' => __('Tiers'),
                                'collapsible' => true,
                                'opened' => true,
                                'componentType' => Form\Fieldset::NAME,
                                'dataScope' => self::DATA_SCOPE_TIER,
                                'disabled' => false,
                                'sortOrder' => $this->getNextGroupSortOrder(
                                    $meta,
                                    'conditions',
                                    self::SORT_ORDER
                                )
                            ],
                        ],
                    ],
                    'children' => $this->getTierFieldsets(),
                ],
            ]
        );

        return $meta;
    }

    /**
     * @return array
     */
    protected function getTierFieldsets()
    {
        $children = [];
        $tierList = $this->getTierList();
        foreach ($tierList as $tier) {
            $children[$tier->getId()] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'additionalClasses' => 'admin__fieldset-actions-tiers',
                            'label' => $tier->getName(),
                            'collapsible' => true,
                            'componentType' => Form\Fieldset::NAME,
                            'dataScope' => $tier->getId(),
                            'disabled' => false,
                            'sortOrder' => $tier->getMinEarnPoints()
                        ],
                    ],
                ],
                'children' => $this->getFieldsForFieldset($tier->getId()),
            ];
        }

        return $children;
    }

    /**
     * @param int $tierId
     * @return array
     */
    abstract protected function getFieldsForFieldset($tierId);

    /**
     * @return \Mirasvit\Rewards\Api\Data\TierInterface[]
     */
    protected function getTierList()
    {
        if (!empty($this->tierList)) {
            return $this->tierList;
        }
        $this->tierList = [];
        $tiersList = $this->helperTier->getTierList();

        foreach ($tiersList as $tier) {
            $this->tierList[] = $tier;
        }

        return $this->tierList;
    }

    /**
     * @return array
     */
    protected function getWebsitesValues()
    {
        return $this->helperTier->getTierList();
    }
}
