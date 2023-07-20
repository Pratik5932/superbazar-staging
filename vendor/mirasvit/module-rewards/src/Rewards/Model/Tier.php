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



namespace Mirasvit\Rewards\Model;

use Mirasvit\Rewards\Api\Data\TierInterface;
use Mirasvit\Rewards\Helper\Json;
use Mirasvit\Rewards\Helper\Storeview;

class Tier extends \Magento\Framework\Model\AbstractModel implements TierInterface
{
    /**
     * @inheritdoc
     */
    protected $_eventPrefix = 'rewards_tier';
    /**
     * @var Json
     */
    private $jsonHelper;
    /**
     * @var Storeview
     */
    private $storeviewHelper;

    public function __construct(
        Json $jsonHelper,
        Storeview $storeviewHelper,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->jsonHelper = $jsonHelper;
        $this->storeviewHelper = $storeviewHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rewards\Model\ResourceModel\Tier');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->storeviewHelper->getStoreViewValue($this, self::KEY_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->storeviewHelper->setStoreViewValue($this, self::KEY_NAME, $name);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->storeviewHelper->getStoreViewValue($this, self::KEY_DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->storeviewHelper->setStoreViewValue($this, self::KEY_DESCRIPTION, $description);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return $this->getData(self::KEY_IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::KEY_IS_ACTIVE, $isActive);
    }

    /**
     * {@inheritdoc}
     */
    public function getMinEarnPoints()
    {
        return $this->getData(self::KEY_MIN_EARN_POINTS);
    }

    /**
     * {@inheritdoc}
     */
    public function setMinEarnPoints($points)
    {
        return $this->setData(self::KEY_MIN_EARN_POINTS, $points);
    }

    /**
     * {@inheritdoc}
     */
    public function getTierLogo()
    {
        return $this->storeviewHelper->getStoreViewValue($this, self::KEY_TIER_LOGO);
    }

    /**
     * {@inheritdoc}
     */
    public function setTierLogo($logo)
    {
        $this->storeviewHelper->setStoreViewValue($this, self::KEY_TIER_LOGO, $logo);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateId()
    {
        return $this->storeviewHelper->getStoreViewValue($this, self::KEY_TEMPLATE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setTemplateId($templateId)
    {
        $this->storeviewHelper->setStoreViewValue($this, self::KEY_TEMPLATE_ID, $templateId);

        return $this;
    }

    /**
     * @return int
     */
    public function getTierId()
    {
        return $this->getData(self::KEY_TIER_ID);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setTierId($id)
    {
        return $this->setData(self::KEY_TIER_ID, $id);
    }

    /**
     * @return int[]
     */
    public function getWebsiteIds()
    {
        return $this->getData(self::KEY_WEBSITE_IDS);
    }

    /**
     * @param int[] $ids
     * @return $this
     */
    public function setWebsiteIds($ids)
    {
        return $this->setData(self::KEY_WEBSITE_IDS, $ids);
    }

    /**
     * {@inheritdoc}
     */
    public function addData(array $data)
    {
        if (!$this->jsonHelper->isEncoded($data[self::KEY_NAME])) {
            $this->setName($data[self::KEY_NAME]);
            unset($data[self::KEY_NAME]);
        }
        if (!empty($data[self::KEY_TIER_LOGO]) && !$this->jsonHelper->isEncoded($data[self::KEY_TIER_LOGO])) {
            $this->setTierLogo($data[self::KEY_TIER_LOGO]);
            unset($data[self::KEY_TIER_LOGO]);
        }
        if (isset($data[self::KEY_DESCRIPTION]) && !$this->jsonHelper->isEncoded($data[self::KEY_DESCRIPTION])) {
            $this->setDescription($data[self::KEY_DESCRIPTION]);
            unset($data[self::KEY_DESCRIPTION]);
        }
        if (!$this->jsonHelper->isEncoded($data[self::KEY_TEMPLATE_ID])) {
            $this->setTemplateId($data[self::KEY_TEMPLATE_ID]);
            unset($data[self::KEY_TEMPLATE_ID]);
        }

        return parent::addData($data);
    }
}
