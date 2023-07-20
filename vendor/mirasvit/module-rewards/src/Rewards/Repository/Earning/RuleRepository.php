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



namespace Mirasvit\Rewards\Repository\Earning;

use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\SalesRule\Model\Converter\ToDataModel;
use Magento\SalesRule\Model\Converter\ToModel;
use Mirasvit\Rewards\Api\Data\Earning\RuleInterface;
use Mirasvit\Rewards\Api\Data\Earning\RuleSearchResultsInterfaceFactory;
use Mirasvit\Rewards\Api\Repository\Earning\RuleRepositoryInterface;
use Mirasvit\Rewards\Helper\Json;
use Mirasvit\Rewards\Model\Earning\RuleFactory as RuleModelFactory;
use Mirasvit\Rewards\Model\Earning\Rule as RuleModel;
use Mirasvit\Rewards\Model\Earning\Data\RuleFactory;
use Mirasvit\Rewards\Model\Earning\Data\Rule;
use Mirasvit\Rewards\Model\Earning\Data\TierFactory;
use Mirasvit\Rewards\Model\Data\StoreviewDataFactory;
use Mirasvit\Rewards\Model\ResourceModel\Earning\Rule as RuleResource;
use Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\CollectionFactory as EarningRuleCollectionFactory;

class RuleRepository implements RuleRepositoryInterface
{
    use \Mirasvit\Rewards\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rewards\Repository\RepositoryFunction\GetList;

    /**
     * @var Rule[]
     */
    protected $instances = [];
    /**
     * @var Json
     */
    private $jsonHelper;
    /**
     * @var EarningRuleCollectionFactory
     */
    private $ruleCollectionFactory;
    /**
     * @var RuleFactory
     */
    private $objectApiFactory;
    /**
     * @var RuleModelFactory
     */
    private $objectFactory;
    /**
     * @var RuleResource
     */
    private $ruleResource;
    /**
     * @var RuleSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;
    /**
     * @var CustomerFactory
     */
    private $customerFactory;
    /**
     * @var TierFactory
     */
    private $tierFactory;
    /**
     * @var StoreviewDataFactory
     */
    private $storeviewDataFactory;
    /**
     * @var ToDataModel
     */
    private $toDataModel;
    /**
     * @var ToModel
     */
    private $toModelConverter;
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    public function __construct(
        Json $jsonHelper,
        ProductMetadataInterface $productMetadata,
        EarningRuleCollectionFactory $ruleCollectionFactory,
        RuleModelFactory $objectFactory,
        RuleFactory $objectApiFactory,
        RuleResource $ruleResource,
        RuleSearchResultsInterfaceFactory $searchResultsFactory,
        CustomerFactory $customerFactory,
        TierFactory $tierFactory,
        StoreviewDataFactory $storeviewDataFactory,
        ToModel $toModelConverter,
        ToDataModel $toDataModel
    ) {
        $this->jsonHelper            = $jsonHelper;
        $this->productMetadata       = $productMetadata;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->objectFactory         = $objectFactory;
        $this->objectApiFactory      = $objectApiFactory;
        $this->ruleResource          = $ruleResource;
        $this->searchResultsFactory  = $searchResultsFactory;
        $this->customerFactory       = $customerFactory;
        $this->tierFactory           = $tierFactory;
        $this->storeviewDataFactory  = $storeviewDataFactory;
        $this->toDataModel           = $toDataModel;
        $this->toModelConverter      = $toModelConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(RuleInterface $rule)
    {
        $ruleModel = $this->objectFactory->create();
        if ($rule->getRuleId()) {
            $this->ruleResource->load($ruleModel, $rule->getRuleId(), RuleInterface::KEY_ID);
        }
        $this->dataRuleToRuleModel($rule, $ruleModel);
        $this->ruleResource->save($ruleModel);

        $this->mapFields($rule, $ruleModel);

        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function get($ruleId)
    {
        if (!isset($this->instances[$ruleId])) {
            /** @var RuleModel $rule */
            $rule = $this->objectFactory->create();
            $this->ruleResource->load($rule, $ruleId);
            if (!$rule->getId()) {
                throw NoSuchEntityException::singleField('id', $ruleId);
            }

            $dataModel = $this->objectApiFactory->create(['data' => $rule->getData()]);

            $this->mapFields($dataModel, $rule);

            $this->instances[$ruleId] = $dataModel;
        }

        return $this->instances[$ruleId];
    }

    /**
     * {@inheritdoc}
     */
    public function getRules(SearchCriteriaInterface $searchCriteria = null)
    {
        $rules = [];
        $data = $this->getList($searchCriteria);
        foreach ($data->getItems() as $item) {
            $rules[] = $this->get($item->getId());
        }
        $data->setItems($rules);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(RuleInterface $rule)
    {
        try {
            $ruleId = $rule->getId();
            $ruleModel = $this->objectFactory->create();
            $this->dataRuleToRuleModel($rule, $ruleModel);
            $this->ruleResource->delete($ruleModel);
        } catch (\Exception $e) {
            throw new StateException(__('Cannot delete rule with id %1', $rule->getId()), $e);
        }
        unset($this->instances[$ruleId]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($ruleId)
    {
        $rule = $this->get($ruleId);

        return $this->delete($rule);
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->ruleCollectionFactory->create();
    }

    /**
     * @param Rule      $dataModel
     * @param RuleModel $ruleModel
     * @return $this
     */
    private function mapFields($dataModel, $ruleModel)
    {
        $this->mapConditions($dataModel, $ruleModel);
        $this->mapActionConditions($dataModel, $ruleModel);
        $this->mapTiers($dataModel, $ruleModel);
        $this->mapStoreviewData($dataModel, $ruleModel);

        return $this;
    }

    /**
     * @param Rule      $dataModel
     * @param RuleModel $ruleModel
     * @return $this
     */
    private function mapConditions(Rule $dataModel, RuleModel $ruleModel)
    {
        $conditions = $this->toDataModel->arrayToConditionDataModel($ruleModel->getConditions()->asArray());
        $dataModel->setCondition($conditions);

        return $this;
    }

    /**
     * @param Rule      $dataModel
     * @param RuleModel $ruleModel
     * @return $this
     */
    private function mapActionConditions(Rule $dataModel, RuleModel $ruleModel)
    {
        $actions = $this->toDataModel->arrayToConditionDataModel($ruleModel->getActions()->asArray());
        $dataModel->setAction($actions);

        return $this;
    }

    /**
     * @param Rule      $dataModel
     * @param RuleModel $ruleModel
     * @return $this
     */
    private function mapTiers(Rule $dataModel, RuleModel $ruleModel)
    {
        $dataModel->setTiers($this->arrayToTiersDataModel($ruleModel->getTiersSerialized()));

        return $this;
    }

    /**
     * @param array $data
     * @return array
     */
    private function arrayToTiersDataModel($data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            $value['tier_id'] = $key;
            $tier = $this->tierFactory->create();
            foreach ($value as $k => $v) {
                $tier->setData($k, $v);
            }
            $result[] = $tier;
        }

        return $result;
    }

    /**
     * @param Rule      $dataModel
     * @param RuleModel $ruleModel
     * @return $this
     */
    private function mapStoreviewData(Rule $dataModel, RuleModel $ruleModel)
    {
        $fields = [RuleInterface::KEY_FRONT_NAME, RuleInterface::KEY_HISTORY_MESSAGE, RuleInterface::KEY_EMAIL_MESSAGE];
        foreach ($fields as $field) {
            $arr = $this->jsonHelper->unserialize($ruleModel->getData($field));
            $dataModel->setData($field, $this->arrayToStoreviewDataDataModel($arr));
        }

        return $this;
    }

    /**
     * @param array $data
     * @return array
     */
    private function arrayToStoreviewDataDataModel($data)
    {
        return array_shift($data);
        $result = [];
        foreach ($data as $key => $value) {
            $obj = $this->storeviewDataFactory->create();
            $obj->setStoreviewId($key);
            $obj->setValue($value);
            $result[] = $obj;
        }

        return $result;
    }


    /**
     * @param Rule $dataModel
     * @param RuleModel     $ruleModel
     * @return $this
     */
    private function dataRuleToRuleModel(Rule $dataModel, RuleModel $ruleModel)
    {
        $data = $dataModel->__toArray();

        unset($data[RuleInterface::KEY_CONDITIONS_SERIALIZED]);
        unset($data[RuleInterface::KEY_ACTIONS_SERIALIZED]);

        if ($dataModel->getTiers()) {
            $tiers = [];
            foreach ($data[RuleInterface::KEY_TIERS_SERIALIZED] as $tier) {
                $tierId = $tier['tier_id'];
                unset($tier['tier_id']);
                $tiers[$tierId] = $tier;
            }
            $data[RuleInterface::KEY_TIERS_SERIALIZED] = $this->jsonHelper->serialize($tiers);
        }

        $ruleModel->setData($data);
        if ($dataModel->getCondition()) {
            $conditions = $this->toModelConverter->dataModelToArray($dataModel->getCondition());
            $ruleModel->getConditions()->setConditions([])->loadArray($conditions);
        }
        if ($dataModel->getAction()) {
            $actions = $this->toModelConverter->dataModelToArray($dataModel->getAction());
            $ruleModel->getActions()->setActions([])->loadArray($actions);
        }

        return $this;
    }
}
