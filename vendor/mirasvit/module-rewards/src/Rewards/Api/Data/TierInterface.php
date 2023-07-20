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


namespace Mirasvit\Rewards\Api\Data;

interface TierInterface
{
    const KEY_TIER_ID         = 'tier_id';
    const KEY_NAME            = 'name';
    const KEY_DESCRIPTION     = 'description';
    const KEY_IS_ACTIVE       = 'is_active';
    const KEY_MIN_EARN_POINTS = 'min_earn_points';
    const KEY_TEMPLATE_ID     = 'template_id';
    const KEY_WEBSITE_IDS     = 'website_ids';
    const KEY_TIER_LOGO       = 'tier_logo';

    const CUSTOMER_KEY_TIER_ID = 'mst_rewards_tier_id';

    const TYPE_POINT = 'point';
    const TYPE_ORDER = 'order';

    /**
     * @return int
     */
    public function getTierId();

    /**
     * @param int $id
     * @return $this
     */
    public function setTierId($id);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsActive();

    /**
     * @param bool $isActive
     * @return $this
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function setIsActive($isActive);

    /**
     * @return int
     */
    public function getMinEarnPoints();

    /**
     * @param int $points
     * @return $this
     */
    public function setMinEarnPoints($points);

    /**
     * @return string
     */
    public function getTierLogo();

    /**
     * @param string $logo
     * @return $this
     */
    public function setTierLogo($logo);

    /**
     * @return int
     */
    public function getTemplateId();

    /**
     * @param int $templateId
     * @return $this
     */
    public function setTemplateId($templateId);

    /**
     * @return int[]
     */
    public function getWebsiteIds();
    /**
     * @param int[] $ids
     * @return $this
     */
    public function setWebsiteIds($ids);
}
