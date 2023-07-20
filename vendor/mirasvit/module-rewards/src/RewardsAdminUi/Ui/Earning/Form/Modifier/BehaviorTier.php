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
use Mirasvit\RewardsAdminUi\Ui\Earning\Form\Source\EarningCartStyle;
use Magento\Framework\Registry;
use Magento\Customer\Model\ResourceModel\Group\Collection as GroupCollection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Ui\Component\Form;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BehaviorTier extends Tier
{
    const DATA_SCOPE_EARNING_STYLE = 'earning_style';
    const DATA_SCOPE_EARNING_POINTS = 'earn_points';
    const DATA_SCOPE_MONETARY_STEP = 'monetary_step';
    const DATA_SCOPE_QTY_SPENT = 'qty_step';
    const DATA_SCOPE_POINTS_LIMIT = 'points_limit';
    const DATA_SCOPE_TRANSFER_TO_GROUP = 'transfer_to_group';

    const DATA_NAME_EARNING_STYLE = 'earning_style';
    const DATA_NAME_EARNING_POINTS = 'earn_points';
    const DATA_NAME_MONETARY_STEP = 'monetary_step';
    const DATA_NAME_QTY_SPENT = 'qty_step';
    const DATA_NAME_POINTS_LIMIT = 'points_limit';
    const DATA_NAME_TRANSFER_TO_GROUP = 'transfer_to_group';

    const SORT_ORDER = 20;

    private $grooupOptions;

    public function __construct(
        EarningCartStyle $earningStyleOption,
        HelperTier $helperTier,
        TierRepositoryInterface $tierRepository,
        Registry $registry,
        GroupCollection $grooupOptions,
        StoreManagerInterface $storeManager,
        WebsiteRepositoryInterface $websiteRepository,
        GroupRepositoryInterface $groupRepository,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->grooupOptions = $grooupOptions;

        parent::__construct($earningStyleOption, $helperTier, $tierRepository, $registry, $storeManager,
            $websiteRepository, $groupRepository, $storeRepository);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTierData($tier)
    {
        return [
            self::DATA_SCOPE_EARNING_STYLE     => $tier[self::DATA_SCOPE_EARNING_STYLE],
            self::DATA_SCOPE_EARNING_POINTS    => $tier[self::DATA_SCOPE_EARNING_POINTS],
            self::DATA_SCOPE_MONETARY_STEP     => $tier[self::DATA_SCOPE_MONETARY_STEP],
            self::DATA_SCOPE_QTY_SPENT         => !empty($tier[self::DATA_SCOPE_QTY_SPENT]) ? $tier[self::DATA_SCOPE_QTY_SPENT] : 0,
            self::DATA_SCOPE_POINTS_LIMIT      => $tier[self::DATA_SCOPE_POINTS_LIMIT],
            self::DATA_SCOPE_TRANSFER_TO_GROUP => !empty($tier[self::DATA_SCOPE_TRANSFER_TO_GROUP]) ? $tier[self::DATA_SCOPE_TRANSFER_TO_GROUP] : '',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultTierData()
    {
        return [
            self::DATA_SCOPE_EARNING_STYLE     => 0,
            self::DATA_SCOPE_EARNING_POINTS    => 0,
            self::DATA_SCOPE_MONETARY_STEP     => 0,
            self::DATA_SCOPE_QTY_SPENT         => 0,
            self::DATA_SCOPE_POINTS_LIMIT      => 0,
            self::DATA_SCOPE_TRANSFER_TO_GROUP => '',
        ];
    }

    /**
     * @param int $tierId
     * @return array
     */
    protected function getFieldsForFieldset($tierId)
    {
        $children = [];
        $children[$this->getKeyEarningStyle($tierId)] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'component' => 'Mirasvit_RewardsAdminUi/js/form/fields/earningbehavioroptions',
                        'componentType' => Form\Field::NAME,
                        'dataType' => Form\Element\Input::NAME,
                        'formElement' => Form\Element\Select::NAME,
                        'label' => __('Customer Earning Style'),
                        'additionalInfo' => __('Please, note, for the events "Customer has a birthday" & "Customer is not active for a long time" (executed by cron) you can only use the "Give X points to customer" style'),
                        'dataScope' => self::DATA_SCOPE_EARNING_STYLE,
                        'validation' => [
                            'required-entry' => true,
                        ],
                    ],
                    'visibleValue' => 'referred_customer_order',
                    'options' => $this->earningStyleOption->toOptionArray(),
                ],
            ],
        ];
        $children[$this->getKeyEarningPoints($tierId)] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'dataType' => Form\Element\DataType\Number::NAME,
                        'formElement' => Form\Element\Input::NAME,
                        'label' => __('Number of Points (X)'),
                        'dataScope' => self::DATA_SCOPE_EARNING_POINTS,
                        'validation' => [
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
                        'label' => __('Step (Y)'),
                        'additionalInfo' => __('In base currency'),
                        'dataScope' => self::DATA_SCOPE_MONETARY_STEP,
                        'validation' => [
                            'required-entry' => true,
                        ],
                        'visibleValue' => 'earning_style_amount_spent',
                    ],
                ],
            ],
        ];
        $children[$this->getKeyQtySpent($tierId)] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'dataType' => Form\Element\DataType\Number::NAME,
                        'formElement' => Form\Element\Input::NAME,
                        'label' => __('Quantity Step (Z)'),
                        'dataScope' => self::DATA_SCOPE_QTY_SPENT,
                        'validation' => [
                            'required-entry' => true,
                        ],
                        'visibleValue' => 'earning_style_qty_spent',
                    ],
                ],
            ],
        ];
        $children[$this->getKeyPointsLimit($tierId)] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'dataType' => Form\Element\DataType\Number::NAME,
                        'formElement' => Form\Element\Input::NAME,
                        'label' => __('Maximum number of earned points for one customer per day'),
                        'additionalInfo' => __('Set 0 to disable limit'),
                        'dataScope' => self::DATA_SCOPE_POINTS_LIMIT,
                    ],
                ],
            ],
        ];
        $groups = $this->filterGroups($this->grooupOptions->toOptionArray());
        array_unshift($groups, ['value' => '', 'label' => __('Do not change')->getText()]);
        $children[$this->getKeyTransferGroup($tierId)] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'dataType' => Form\Element\Input::NAME,
                        'formElement' => Form\Element\Select::NAME,
                        'label' => __('Assign customer to Group'),
                        'dataScope' => self::DATA_SCOPE_TRANSFER_TO_GROUP,
                    ],
                    'options' => $groups,
                ],
            ],
        ];

        return $children;
    }

    /**
     * @param array $groups
     * @return array
     */
    private function filterGroups($groups)
    {
        foreach ($groups as $k => $group) {
            if ($group['label'] == 'NOT LOGGED IN') {
                unset($groups[$k]);
            }
        }

        return $groups;
    }

    /**
     * @param int $tierId
     * @return string
     */
    protected function getKeyEarningStyle($tierId)
    {
        return self::DATA_SCOPE_TIER . '.' . $tierId . '.' . self::DATA_NAME_EARNING_STYLE;
    }

    /**
     * @param int $tierId
     * @return string
     */
    protected function getKeyEarningPoints($tierId)
    {
        return self::DATA_SCOPE_TIER . '.' . $tierId . '.' . self::DATA_NAME_EARNING_POINTS;
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
    protected function getKeyQtySpent($tierId)
    {
        return self::DATA_SCOPE_TIER . '.' . $tierId . '.' . self::DATA_NAME_QTY_SPENT;
    }

    /**
     * @param int $tierId
     * @return string
     */
    protected function getKeyPointsLimit($tierId)
    {
        return self::DATA_SCOPE_TIER . '.' . $tierId . '.' . self::DATA_NAME_POINTS_LIMIT;
    }

    /**
     * @param int $tierId
     * @return string
     */
    protected function getKeyTransferGroup($tierId)
    {
        return self::DATA_SCOPE_TIER . '.' . $tierId . '.' . self::DATA_NAME_TRANSFER_TO_GROUP;
    }
}
