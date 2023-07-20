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



namespace Mirasvit\Rewards\Plugin\Customer\Model;

use Magento\Framework\View\TemplateEngineFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Customer\Model\Attribute;
use Mirasvit\Rewards\Api\Data\TierInterface;

/**
 * Set tier model to the customer's tier attribute
 */
class AttributePlugin
{
    /**
     * @var string
     */
    CONST TIER_MODEL = 'Mirasvit\Rewards\Model\Customer\Entity\Attribute\Source\Tier';

    /**
     * @return \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetSource(Attribute $subject, \Closure $proceed)
    {
        if ($subject->getAttributeCode() == TierInterface::CUSTOMER_KEY_TIER_ID) {
            $subject->setSourceModel(self::TIER_MODEL);
        }
        $result = $proceed();

        return $result;
    }
}
