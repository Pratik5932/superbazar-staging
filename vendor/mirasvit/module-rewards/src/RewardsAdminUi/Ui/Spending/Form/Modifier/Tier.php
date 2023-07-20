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


namespace Mirasvit\RewardsAdminUi\Ui\Spending\Form\Modifier;

use Mirasvit\Rewards\Api\Repository\TierRepositoryInterface;
use Mirasvit\Rewards\Helper\Tier\Option as HelperTier;
use Mirasvit\RewardsAdminUi\Ui\Spending\Form\Source\SpendingStyle;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Ui\Component\Form;
use Magento\Framework\Registry;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Tier extends AbstractModifier
{
    const DATA_SCOPE_SPENDING_STYLE = 'spending_style';
    const DATA_SCOPE_SPEND_POINTS = 'spend_points';
    const DATA_SCOPE_MONETARY_STEP = 'monetary_step';
    const DATA_SCOPE_SPEND_MIN_POINTS = 'spend_min_points';
    const DATA_SCOPE_SPEND_MAX_POINTS = 'spend_max_points';

    const DATA_NAME_SPENDING_STYLE = 'spending_style';
    const DATA_NAME_SPEND_POINTS = 'spend_points';
    const DATA_NAME_MONETARY_STEP = 'monetary_step';
    const DATA_NAME_SPEND_MIN_POINTS = 'spend_min_points';
    const DATA_NAME_SPEND_MAX_POINTS = 'spend_max_points';

    const SORT_ORDER = 20;

    private $spendingProductStyleOption;
    private $helperTier;
    private $tierRepository;
    private $registry;
    private $storeManager;
    private $websiteRepository;
    private $groupRepository;
    private $storeRepository;

    public function __construct(
        SpendingStyle $spendingProductStyleOption,
        HelperTier $helperTier,
        TierRepositoryInterface $tierRepository,
        Registry $registry,
        StoreManagerInterface $storeManager,
        WebsiteRepositoryInterface $websiteRepository,
        GroupRepositoryInterface $groupRepository,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->spendingProductStyleOption = $spendingProductStyleOption;
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
        /** @var \Mirasvit\Rewards\Model\Spending\Rule $rule */
        $rule = $this->registry->registry('current_spending_rule');

        if ($rule->getId()) {
            $tiers = $rule->getTiersSerialized();
            if ($tiers) {
                foreach ($this->getTierList() as $tier) {
                    $tierId = $tier->getId();
                    if (isset($tiers[$tierId])) {
                        $data[$rule->getId()]['tier'][$tierId] = [
                            self::DATA_SCOPE_SPENDING_STYLE   => $tiers[$tierId][self::DATA_SCOPE_SPENDING_STYLE],
                            self::DATA_SCOPE_SPEND_POINTS     => $tiers[$tierId][self::DATA_SCOPE_SPEND_POINTS],
                            self::DATA_SCOPE_MONETARY_STEP    => $tiers[$tierId][self::DATA_SCOPE_MONETARY_STEP],
                            self::DATA_SCOPE_SPEND_MIN_POINTS => $tiers[$tierId][self::DATA_SCOPE_SPEND_MIN_POINTS],
                            self::DATA_SCOPE_SPEND_MAX_POINTS => $tiers[$tierId][self::DATA_SCOPE_SPEND_MAX_POINTS],
                        ];
                    } else {
                        $data[$rule->getId()]['tier'][$tierId] = [
                            self::DATA_SCOPE_SPENDING_STYLE   => 0,
                            self::DATA_SCOPE_SPEND_POINTS     => 0,
                            self::DATA_SCOPE_MONETARY_STEP    => 0,
                            self::DATA_SCOPE_SPEND_MIN_POINTS => 0,
                            self::DATA_SCOPE_SPEND_MAX_POINTS => 0,
                        ];
                    }
                }
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $meta = array_replace_recursive(
            $meta,
            [
                'tiers' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'additionalClasses' => 'admin__fieldset-actions',
                                'label' => __('Actions'),
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
                            'label'             => $tier->getName(),
                            'collapsible'       => true,
                            'componentType'     => Form\Fieldset::NAME,
                            'dataScope'         => $tier->getId(),
                            'disabled'          => false,
                            'sortOrder'         => $tier->getMinEarnPoints()
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
    protected function getFieldsForFieldset($tierId)
    {
        $children = [];
        $children[$this->getKeySpendingStyle($tierId)] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'dataType'      => Form\Element\Input::NAME,
                        'formElement'   => Form\Element\Select::NAME,
                        'label'         => __('Points spending style'),
                        'dataScope'     => self::DATA_SCOPE_SPENDING_STYLE,
                        'validation'    => [
                            'required-entry' => true,
                        ],
                    ],
                    'options' => $this->spendingProductStyleOption->toOptionArray(),
                ],
            ],
        ];
        $children[$this->getKeySpendingPoints($tierId)] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'dataType'      => Form\Element\DataType\Number::NAME,
                        'formElement'   => Form\Element\Input::NAME,
                        'label'         => __('For each spent X points'),
                        'additionalInfo' => __('Number of points.'),
                        'dataScope'     => self::DATA_SCOPE_SPEND_POINTS,
                        'validation'    => [
                            'required-entry' => true,
                        ],
                    ],
                ],
            ],
        ];
        $children[$this->getKeyMonetaryStep($tierId)] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'dataType' => Form\Element\DataType\Number::NAME,
                        'formElement' => Form\Element\Input::NAME,
                        'label' => __('Customer receive Y discount'),
                        'additionalInfo' => __('You can enter amount in base currency or percent. e.g. 100 or 5%.'),
                        'dataScope' => self::DATA_SCOPE_MONETARY_STEP,
                        'validation' => [
                            'required-entry' => true,
                        ],
                    ],
                ],
            ],
        ];
        $help = __('You can enter amount of points or percent. e.g. 100 or 5%. Leave empty to disable.');
        $children[$this->getKeyPointsMin($tierId)] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'dataType' => Form\Element\DataType\Number::NAME,
                        'formElement' => Form\Element\Input::NAME,
                        'label' => __('Spend minimum'),
                        'additionalInfo' => $help,
                        'dataScope' => self::DATA_SCOPE_SPEND_MIN_POINTS,
                    ],
                ],
            ],
        ];
        $help = __('You can enter amount of points or percent. e.g. 100 or 5%. Leave empty to disable.');
        $children[$this->getKeyPointsMax($tierId)] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'dataType' => Form\Element\DataType\Number::NAME,
                        'formElement' => Form\Element\Input::NAME,
                        'label' => __('Spend maximum'),
                        'additionalInfo' => $help,
                        'dataScope' => self::DATA_SCOPE_SPEND_MAX_POINTS,
                    ],
                ],
            ],
        ];

        return $children;
    }

    /**
     * @param int $tierId
     * @return string
     */
    protected function getKeySpendingStyle($tierId)
    {
        return self::DATA_SCOPE_TIER . '.' . $tierId . '.' . self::DATA_NAME_SPENDING_STYLE;
    }

    /**
     * @param int $tierId
     * @return string
     */
    protected function getKeySpendingPoints($tierId)
    {
        return self::DATA_SCOPE_TIER . '.' . $tierId . '.' . self::DATA_NAME_SPEND_POINTS;
    }

    /**
     * @param int $tierId
     * @return string
     */
    protected function getKeyMonetaryStep($tierId)
    {
        return self::DATA_SCOPE_TIER . '.' . $tierId . '.' . self::DATA_NAME_MONETARY_STEP;
    }

    /**
     * @param int $tierId
     * @return string
     */
    protected function getKeyPointsMin($tierId)
    {
        return self::DATA_SCOPE_TIER . '.' . $tierId . '.' . self::DATA_NAME_SPEND_MIN_POINTS;
    }

    /**
     * @param int $tierId
     * @return string
     */
    protected function getKeyPointsMax($tierId)
    {
        return self::DATA_SCOPE_TIER . '.' . $tierId . '.' . self::DATA_NAME_SPEND_MAX_POINTS;
    }

    private $tierList;
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
