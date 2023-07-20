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



namespace Mirasvit\RewardsAdminUi\Controller\Adminhtml\Earning\Rule;

use Mirasvit\Core\Service\CompatibilityService;
use Mirasvit\RewardsAdminUi\Ui\Earning\Form\Modifier\AbstractModifier;
use Mirasvit\Rewards\Api\Data\Earning\RuleInterface;
use Mirasvit\Rewards\Helper\Json as JsonHelper;
use Mirasvit\Rewards\Helper\Serializer;
use Mirasvit\Rewards\Model\Earning\RuleFactory;
use Magento\Framework\Stdlib\DateTime\Filter\Date as DateFilter;
use Mirasvit\Rewards\Service\Rule\TierValidationService;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Registry;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
    /**
    * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
    */
class Save extends \Mirasvit\RewardsAdminUi\Controller\Adminhtml\Earning\Rule
{
    protected $typeList;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        JsonHelper $jsonHelper,
        Serializer $serializer,
        RuleFactory $earningRuleFactory,
        DateFilter $dateFilter,
        TierValidationService $tierValidationService,
        TimezoneInterface $localeDate,
        Registry $registry,
        Context $context,
        TypeListInterface $typeList
    ) {
        parent::__construct($jsonHelper, $serializer, $earningRuleFactory, $tierValidationService, $dateFilter,
            $localeDate, $registry, $context);

        $this->typeList           = $typeList;
    }
    public function execute()
    {
        if ($data = $this->getRequest()->getParams()) {
            $data = $this->prepareData($data);
            $earningRule = $this->_initEarningRule();

            $earningRule->addData($data);
            if (isset($data['rule'])) {
                $earningRule->loadPost($data['rule']);
            }

            try {
                $this->validateTierData($earningRule);
                $earningRule->save();
                $this->typeList->invalidate(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
                $this->messageManager->addSuccessMessage(__('Earning Rule was successfully saved'));
                $this->backendSession->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $path = $this->getEditPath($earningRule);
                    if (!$path) {
                        $this->messageManager->addErrorMessage('Data not set');
                        $this->_redirect('*/*/add');
                        return;
                    }
                    $this->_redirect(
                        $path, ['id' => $earningRule->getId(), 'store' => $earningRule->getStoreId()]
                    );
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $path = $this->getEditPath($earningRule);
                if ($path) {
                    $this->backendSession->setFormData($data);
                    $this->_redirect($path, ['id' => $this->getRequest()->getParam('id')]);
                } else {
                    $this->_redirect('*/*/index');
                }
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find Earning Rule to save'));
        $this->_redirect('*/*/');
    }

    /**
     * @param \Mirasvit\Rewards\Model\Earning\Rule $rule
     * @return string
     */
    private function getEditPath($rule)
    {
        $type = $rule->getType();
        switch ($type) {
            case 'cart':
                $path = '*/*/editCart';
                break;
            case 'product':
                $path = '*/*/editProduct';
                break;
            case 'behavior':
                $path = '*/*/editBehavior';
                break;
            default:
                $path = '';
                break;
        }
        return $path;
    }

    /**
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function prepareData($data)
    {
        $dataValues = ['monetary_step', 'qty_step', 'points_limit', 'param1', AbstractModifier::DATA_SCOPE_TIER];

        foreach ($dataValues as $value) {
            if (isset($data[$value])
                && !$data[$value]
            ) {
                unset($data[$value]);
            }
        }

        if (isset($data[AbstractModifier::DATA_SCOPE_TIER])) {
            $tiers = $data[AbstractModifier::DATA_SCOPE_TIER];
            $data[RuleInterface::KEY_TIERS_SERIALIZED] = $this->serializer->serialize($tiers);
            unset($data[AbstractModifier::DATA_SCOPE_TIER]);
        }
        $filterValues = [];
        if (!empty($data['active_from'])) {
            $filterValues['active_from'] = $this->dateFilter;
        }
        if (!empty($data['active_to'])) {
            $filterValues['active_to'] = $this->dateFilter;
        }
        if ($filterValues) {
            if ((version_compare(CompatibilityService::getVersion(), "2.4.6", ">="))) {
                $inputFilter = new \Magento\Framework\Filter\FilterInput($filterValues, [], $data);
            } else {
                $inputFilter = new \Zend_Filter_Input($filterValues, [], $data);
            }

            $data        = $inputFilter->getUnescaped();
        }

        return $data;
    }

    /**
     * @param  \Mirasvit\Rewards\Model\Earning\Rule $rule
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function validateTierData($rule)
    {
        $numberFields  = [
            'earn_points'   => __('Number of Points (X)'),
            'qty_step'      => __('Quantity Step (Z)'),
            'monetary_step' => __('Step (Y)'),
        ];

        $data = $rule->getTiersSerialized();
        foreach ($data as $tierData) {
            foreach ($numberFields as $fieldName => $fieldLabel) {
                if (!empty($tierData[$fieldName]) && !$this->tierValidationService->isValidNumberValue($tierData[$fieldName])) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Field "%1" should be valid number, i.e. 5 or 7.5', $fieldLabel->render())
                    );
                }
            }
            if (!empty($tierData['points_limit']) && !$this->tierValidationService->isValidPercentValue($tierData['points_limit'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Field "%1" should be valid number and percent should be equal or less then 100', __('Earn Maximum')->render())
                );
            }
        }
    }
}
