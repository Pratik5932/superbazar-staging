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



namespace Mirasvit\Rewards\Model\Earning\Rule\Condition;

class Rma extends \Magento\Rule\Model\Condition\AbstractCondition
{
    const OPTION_REASON     = 'reason_id';
    const OPTION_CONDITION  = 'condition_id';
    const OPTION_RESOLUTION = 'resolution_id';
    const OPTION_STATUS     = 'status_id';
    /**
     * @var \Magento\Rule\Model\Condition\Context
     */
    protected $context;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        array $data = []
    ) {
        $this->context = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            self::OPTION_REASON     => __('Reasons'),
            self::OPTION_CONDITION  => __('Conditions'),
            self::OPTION_RESOLUTION => __('Resolutions'),
            self::OPTION_STATUS     => __('Statuses'),
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        $type = 'string';

        switch ($this->getAttribute()) {
            case self::OPTION_REASON:
            case self::OPTION_CONDITION:
            case self::OPTION_RESOLUTION:
            case self::OPTION_STATUS:
                $type = 'select';
                break;
        }

        return $type;
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        $type = 'text';

        switch ($this->getAttribute()) {
            case self::OPTION_REASON:
            case self::OPTION_CONDITION:
            case self::OPTION_RESOLUTION:
            case self::OPTION_STATUS:
                $type = 'select';
                break;
        }

        return $type;
    }

    /**
     * @return array
     */
    public function getValueSelectOptions()
    {
        $opt = parent::getValueSelectOptions();

        return array_merge($opt, $this->_prepareValueOptions());
    }

    /**
     * @return array
     */
    protected function _prepareValueOptions()
    {
        $selectOptions = [];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if ($this->getAttribute() === self::OPTION_CONDITION) {
            $condition = $objectManager->create('\Mirasvit\Rma\Model\ResourceModel\Condition\Collection');
            $selectOptions = $condition->toOptionArray();
        }
        if ($this->getAttribute() === self::OPTION_REASON) {
            $reason = $objectManager->create('\Mirasvit\Rma\Model\ResourceModel\Reason\Collection');
            $selectOptions = $reason->toOptionArray();
        }
        if ($this->getAttribute() === self::OPTION_RESOLUTION) {
            $resolution = $objectManager->create('\Mirasvit\Rma\Model\ResourceModel\Resolution\Collection');
            $selectOptions = $resolution->toOptionArray();
        }
        if ($this->getAttribute() === self::OPTION_STATUS) {
            $resolution = $objectManager->create('\Mirasvit\Rma\Model\ResourceModel\Status\Collection');
            $selectOptions = $resolution->toOptionArray();
        }

        return $selectOptions;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @noinspection PhpUndefinedNamespaceInspection */
        if ($object->getRma() && $object->getRma() instanceof \Mirasvit\Rma\Api\Data\RmaInterface) {
            $statusAttributes = [
                self::OPTION_CONDITION,
                self::OPTION_REASON,
                self::OPTION_RESOLUTION,
            ];
            if (in_array($this->getAttribute(), $statusAttributes)) {
                return $this->validateRmaReasons($object->getRma());
            } else {
                /** @var \Magento\Framework\Model\AbstractModel $rma */
                $rma = $object->getRma();
                return parent::validate($rma);
            }
        }

        return parent::validate($object);
    }

    /**
     * @noinspection PhpUndefinedNamespaceInspection
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return bool
     */
    private function validateRmaReasons($rma)
    {
        $result = false;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /** @noinspection PhpUndefinedNamespaceInspection */
        /** @var \Mirasvit\Rma\Api\Service\Item\ItemListBuilderInterface $itemListBuilder */
        $itemListBuilder = $objectManager->create('\Mirasvit\Rma\Api\Service\Item\ItemListBuilderInterface');

        // get offline items
        $items = $itemListBuilder->getRmaItems($rma, false);
        $result = $result || count($items); // if we have items trying to set to true
        foreach ($items as $item) {
            $result = $result && $this->validateAttribute($item->getData($this->getAttribute()));
        }
        // get regular items
        $items = $itemListBuilder->getRmaItems($rma, true);
        $result = $result || count($items); // if we have items trying to set to true
        foreach ($items as $item) {
            $result = $result && $this->validateAttribute($item->getData($this->getAttribute()));
        }

        return $result;
    }
}
