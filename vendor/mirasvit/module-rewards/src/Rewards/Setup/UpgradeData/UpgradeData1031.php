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


namespace Mirasvit\Rewards\Setup\UpgradeData;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Mirasvit\Rewards\Api\Data\Earning\RuleInterface as EarningRuleInterface;
use Mirasvit\Rewards\Api\Data\Earning\RuleInterface;
use Mirasvit\Rewards\Model\Config;

class UpgradeData1031 implements UpgradeDataInterface
{
    /**
     * @var \Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\CollectionFactory
     */
    private $earningCollectionFactory;
    /**
     * @var \Mirasvit\Rewards\Helper\Serializer
     */
    private $serializer;

    public function __construct(
        \Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\CollectionFactory $earningCollectionFactory,
        \Mirasvit\Rewards\Helper\Serializer $serializer
    ) {
        $this->earningCollectionFactory = $earningCollectionFactory;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $collection = $this->earningCollectionFactory->create()
            ->addFieldToFilter(EarningRuleInterface::KEY_TYPE, 'product');
        foreach ($collection as $rule) {
            $table = $setup->getTable('mst_rewards_earning_rule');
            $bind  = $this->getData($rule);
            $where = [$rule->getIdFieldName() . ' = ?' => (int)$rule->getId()];

            $setup->getConnection()->update($table, $bind, $where);
        }
    }

    /**
     * @param \Mirasvit\Rewards\Model\Earning\Rule $rule
     * @return array
     * @throws \Zend_Json_Exception
     */
    private function getData($rule)
    {
        $data = [
            EarningRuleInterface::KEY_TYPE                    => 'cart',
            EarningRuleInterface::KEY_IS_SHOW_ON_PRODUCT_PAGE => 1,
        ];

        $data = array_merge($data, $this->getConditionData($rule));
        $data = array_merge($data, $this->getTierData($rule));

        return $data;
    }

    /**
     * @param \Mirasvit\Rewards\Model\Earning\Rule $rule
     *
     * @return array
     * @throws \Zend_Json_Exception
     */
    private function getConditionData($rule)
    {
        $customerCondition = '\\\\Mirasvit\\\\Rewards\\\\Model\\\\Earning\\\\Rule\\\\Condition\\\\Customer';
        if (strpos($rule->getData('conditions_serialized'), $customerCondition) !== false) {
            return [
                EarningRuleInterface::KEY_NAME      => '[REQUIRE MANUAL UPDATE] ' . $rule->getName(),
                EarningRuleInterface::KEY_IS_ACTIVE => 0,
            ];
        }
        $search      = '\\\\Mirasvit\\\\Rewards\\\\Model\\\\Earning';
        $replacement = 'Magento\\\\SalesRule\\\\Model';

        $rewardsAttributesSearch  = [];
        $rewardsAttributesReplace = [];
        foreach ($this->getProductAttributes() as $k => $label) {
            $rewardsAttributesSearch[]  = '"type":"Magento\\\\SalesRule\\\\Model\\\\Rule\\\\Condition\\\\Product","attribute":"'. $k .'"';
            $rewardsAttributesReplace[] = '"type":"\\\\Mirasvit\\\\Rewards\\\\Model\\\\Earning\\\\Rule\\\\Condition\\\\Product","attribute":"'. $k .'"';
        }
        $rewardsAttributesSearch[]  = '"type":"Magento\\\\SalesRule\\\\Model\\\\Rule\\\\Condition\\\\Customer"';
        $rewardsAttributesReplace[] = '"type":"\\\\Mirasvit\\\\Rewards\\\\Model\\\\Earning\\\\Rule\\\\Condition\\\\Customer"';

        $conditions = $rule->getData('conditions_serialized');
        $array = $this->serializer->unserialize($conditions); // first we decode data
        $json  = $this->serializer->getJsoner()->serialize($array); // then we json them to use str_replace

        $conditions = str_replace($search, $replacement, $json);
        // return back replacement for custom rewards attributes
        $conditions = str_replace($rewardsAttributesSearch, $rewardsAttributesReplace, $conditions);

        if ($this->isSerialized($rule->getData('conditions_serialized'))) {
            $emptyConditions = 'a:6:{s:4:"type";s:54:"\Mirasvit\Rewards\Model\Earning\Rule\Condition\Combine";' .
                's:9:"attribute";N;s:8:"operator";N;s:5:"value";b:1;s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";}';

            $conditions = $this->serializer->unserialize($conditions); // unserialize json
            $conditions = $this->serializer->getSerialiser()->serialize($conditions); // serialize
        } else {
            $emptyConditions = '{"type":"\\\\Mirasvit\\\\Rewards\\\\Model\\\\Earning\\\\Rule\\\\Condition\\\\Combine",' .
                '"attribute":null,"operator":null,"value":true,"is_value_processed":null,"aggregator":"all"}';
        }

        return [
            EarningRuleInterface::KEY_ACTIONS_SERIALIZED    => $conditions,
            EarningRuleInterface::KEY_CONDITIONS_SERIALIZED => $emptyConditions,
        ];
    }

    /**
     * @param \Mirasvit\Rewards\Model\Earning\Rule $rule
     *
     * @return array
     * @throws \Zend_Json_Exception
     */
    private function getTierData($rule)
    {
        $tiers = $rule->getData('tiers_serialized');
        $array = $this->serializer->unserialize($tiers);
        foreach ($array as $k => $tier) {
            $array[$k] = $this->modifyTierEarningStyle($tier);
        }
        $tiers = $this->serializer->serialize($array);

        return [
            EarningRuleInterface::KEY_TIERS_SERIALIZED => $tiers,
        ];
    }

    /**
     * @param array $tier
     *
     * @return array
     */
    private function modifyTierEarningStyle($tier)
    {
        if ($tier[RuleInterface::KEY_TIER_KEY_EARNING_STYLE] == Config::EARNING_STYLE_AMOUNT_PRICE) {
            $tier[RuleInterface::KEY_TIER_KEY_EARNING_STYLE] = Config::EARNING_STYLE_AMOUNT_SPENT;
        }

        return $tier;
    }

    /**
     * @param string $str
     * @return bool
     */
    private function isSerialized($str)
    {
        return strpos($str, 'a:') === 0;
    }

    /**
     * @return array
     */
    private function getProductAttributes()
    {
        return [
            'type_id'          => __('Product Type'),
            'image'            => __('Base Image'),
            'thumbnail'        => __('Thumbnail'),
            'small_image'      => __('Small Image'),
            'image_size'       => __('Base Image Size (bytes)'),
            'thumbnail_size'   => __('Thumbnail Size (bytes)'),
            'small_image_size' => __('Small Image Size (bytes)'),
            'php'              => __('PHP Condition'),
            'price'            => __('Base Price'),
            'final_price'      => __('Final Price'),
            'special_price'    => __('Special Price'),
        ];
    }
}
