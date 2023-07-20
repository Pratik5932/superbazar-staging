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



namespace Mirasvit\RewardsAdminUi\Plugin\Customer\Component\DataProvider\Document;

use Magento\Customer\Ui\Component\DataProvider\Document;
use Magento\Framework\Exception\NoSuchEntityException;
use Mirasvit\Rewards\Api\Repository\TierRepositoryInterface;

/**
 * Set tier name value to customer export document
 */
class CustomTierAttributePlugin
{
    private $tierRepository;

    public function __construct(
        TierRepositoryInterface $tierRepository
    ) {
        $this->tierRepository = $tierRepository;
    }

    /**
     * @param Document $subject
     * @param \Closure $proceed
     * @param string   $code
     *
     * @return \Magento\Framework\Api\AttributeInterface|null
     */
    public function aroundGetCustomAttribute(Document $subject, \Closure $proceed, $code)
    {
        $attribute = $proceed($code);

        if ($code == 'mst_rewards_tier_id') {
            $tier = $this->getTier($attribute);

            if ($tier && $tier->getName()) {
                $attribute->setValue($tier->getName());
            } else {
                $attribute->setValue('');
            }
        }

        return $attribute;
    }

    /**
     * @param \Magento\Framework\Api\AttributeInterface $attribute
     *
     * @return \Mirasvit\Rewards\Api\Repository\TierRepositoryInterface|null
     */
    private function getTier($attribute)
    {
        try {
            $tier = $this->tierRepository->get($attribute->getValue());
        } catch (NoSuchEntityException $e) {
            $tier = null;
        }

        if (!$tier) {
            try {
                $tier = $this->tierRepository->getFirstTier();
            } catch (NoSuchEntityException $e) {
                $tier = null;
            }
        }

        return $tier;
    }
}
