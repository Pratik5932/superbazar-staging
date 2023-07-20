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



namespace Mirasvit\RewardsAdminUi\Controller\Adminhtml\Spending\Rule;

use Mirasvit\Core\Service\CompatibilityService;
use Mirasvit\Rewards\Api\Data\Spending\RuleInterface;
use Mirasvit\RewardsAdminUi\Ui\Spending\Form\Modifier\AbstractModifier;
use Magento\Framework\Controller\ResultFactory;

class Save extends \Mirasvit\RewardsAdminUi\Controller\Adminhtml\Spending\Rule
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        if ($data = $this->getRequest()->getParams()) {
            $spendingRule = $this->_initSpendingRule();

            try {
                $spendingRule->addData($this->prepareData($data));
                if (isset($data['rule'])) {
                    $spendingRule->loadPost($data['rule']);
                }

                $spendingRule->validateTierFields();
                $spendingRule->getResource()->save($spendingRule);
                $this->messageManager->addSuccessMessage(__('Spending Rule was successfully saved'));
                $this->backendSession->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect(
                        '*/*/edit',
                        ['id' => $spendingRule->getId(), 'store' => $spendingRule->getStoreId()]
                    );

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->backendSession->setFormData($data);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('spending_rule_id')]);

                return;
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find Spending Rule to save'));
        $this->_redirect('*/*/');
    }

    /**
     * @param array $data
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function prepareData($data)
    {
        if (isset($data[AbstractModifier::DATA_SCOPE_TIER])) {
            $percentFields  = [
                RuleInterface::KEY_TIER_KEY_MONETARY_STEP    => __('Customer receive Y discount'),
                RuleInterface::KEY_TIER_KEY_SPEND_MIN_POINTS => __('Spend minimum'),
                RuleInterface::KEY_TIER_KEY_SPEND_MAX_POINTS => __('Spend maximum'),
            ];

            $tiers = $data[AbstractModifier::DATA_SCOPE_TIER];
            foreach ($tiers as $tierData) {
                foreach ($percentFields as $fieldName => $fieldLabel) {
                    if (!empty($tierData[$fieldName]) &&
                        !$this->tierValidationService->isValidPercentValue($tierData[$fieldName])
                    ) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Field "%1" should be valid number and percent should be equal or less then 100',
                                $fieldLabel->render())
                        );
                    }
                }
            }
            $data[RuleInterface::KEY_TIERS_SERIALIZED] = $this->jsonHelper->serialize($tiers);

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
}
