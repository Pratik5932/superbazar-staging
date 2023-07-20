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
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Ui\Component\Form;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartTier extends Tier
{
    const DATA_SCOPE_EARNING_STYLE = 'earning_style';
    const DATA_SCOPE_EARNING_POINTS = 'earn_points';
    const DATA_SCOPE_MONETARY_STEP = 'monetary_step';
    const DATA_SCOPE_QTY_SPENT = 'qty_step';
    const DATA_SCOPE_POINTS_LIMIT = 'points_limit';

    const DATA_NAME_EARNING_STYLE = 'earning_style';
    const DATA_NAME_EARNING_POINTS = 'earn_points';
    const DATA_NAME_MONETARY_STEP = 'monetary_step';
    const DATA_NAME_QTY_SPENT = 'qty_step';
    const DATA_NAME_POINTS_LIMIT = 'points_limit';

    const SORT_ORDER = 20;

    public function __construct(
        EarningCartStyle $earningStyleOption,
        HelperTier $helperTier,
        TierRepositoryInterface $tierRepository,
        Registry $registry,
        StoreManagerInterface $storeManager,
        WebsiteRepositoryInterface $websiteRepository,
        GroupRepositoryInterface $groupRepository,
        StoreRepositoryInterface $storeRepository
    ) {
        parent::__construct($earningStyleOption, $helperTier, $tierRepository, $registry, $storeManager,
            $websiteRepository, $groupRepository, $storeRepository);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTierData($tier)
    {
        return [
            self::DATA_SCOPE_EARNING_STYLE  => $tier[self::DATA_SCOPE_EARNING_STYLE],
            self::DATA_SCOPE_EARNING_POINTS => $tier[self::DATA_SCOPE_EARNING_POINTS],
            self::DATA_SCOPE_MONETARY_STEP  => $tier[self::DATA_SCOPE_MONETARY_STEP],
            self::DATA_SCOPE_QTY_SPENT      => !empty($tier[self::DATA_SCOPE_QTY_SPENT]) ? $tier[self::DATA_SCOPE_QTY_SPENT] : 0,
            self::DATA_SCOPE_POINTS_LIMIT   => $tier[self::DATA_SCOPE_POINTS_LIMIT],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultTierData()
    {
        return [
            self::DATA_SCOPE_EARNING_STYLE  => 0,
            self::DATA_SCOPE_EARNING_POINTS => 0,
            self::DATA_SCOPE_MONETARY_STEP  => 0,
            self::DATA_SCOPE_QTY_SPENT      => 0,
            self::DATA_SCOPE_POINTS_LIMIT   => 0,
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
                        'component' => 'Mirasvit_RewardsAdminUi/js/form/fields/earningcartoptions',
                        'componentType' => Form\Field::NAME,
                        'dataType' => Form\Element\Input::NAME,
                        'formElement' => Form\Element\Select::NAME,
                        'label' => __('Customer Earning Style'),
                        'dataScope' => self::DATA_SCOPE_EARNING_STYLE,
                        'validation' => [
                            'required-entry' => true,
                        ],
                    ],
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
                        'label' => __('Earn Maximum'),
                        'additionalInfo' => __('You can enter amount of points. Leave empty to disable.'),
                        'dataScope' => self::DATA_SCOPE_POINTS_LIMIT,
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
}
