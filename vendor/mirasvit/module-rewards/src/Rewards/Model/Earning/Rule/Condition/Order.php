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

use Magento\Rule\Model\Condition\AbstractCondition;

class Order extends AbstractCondition
{
    /**
     * @var \Magento\Rule\Model\Condition\Context
     */
    private $context;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        array $data = []
    ) {
        $this->context = $context;

        parent::__construct($context, $data);
    }

    const OPTION_ORDERS_SUBTOTAL = 'subtotal';

    /**
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            self::OPTION_ORDERS_SUBTOTAL => __('Subtotal'),
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getCustomerOrder()) {
            return parent::validate($object->getCustomerOrder());
        }

        return parent::validate($object);
    }
}
