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



namespace Mirasvit\Rewards\Model\ResourceModel;

class Transaction extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Mirasvit\Rewards\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Context
     */
    protected $context;

    /**
     * @var string
     */
    protected $resourcePrefix;

    /**
     * @param \Mirasvit\Rewards\Model\Config                    $config
     * @param \Magento\Framework\Stdlib\DateTime\DateTime       $date
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string                                            $resourcePrefix
     */
    public function __construct(
        \Mirasvit\Rewards\Model\Config $config,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $resourcePrefix = null
    ) {
        $this->config = $config;
        $this->date = $date;
        $this->context = $context;
        $this->resourcePrefix = $resourcePrefix;
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mst_rewards_transaction', 'transaction_id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var  \Mirasvit\Rewards\Model\Transaction $object */
        if (!$object->getIsMassDelete()) {
        }

        return parent::_afterLoad($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var  \Mirasvit\Rewards\Model\Transaction $object */
        if (!$object->getId()) {
            if (!$object->getCreatedAt()) {
                $object->setCreatedAt(
                    (new \DateTime( "now", new \DateTimeZone( "UTC" ) ))->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
                );
            }
            if ($object->getAmount() > 0) {
                $activatedAtDays = $this->config->getGeneralActivatesAfterDays();
                if ($activatedAtDays > 0 && $object->getIsAllowPending()) {
                    $activatedAt = $this->date->gmtDate(
                        'Y-m-d', $this->date->gmtTimestamp() + $activatedAtDays * 24 * 60 * 60
                    );
                    $object->setActivatedAt($activatedAt);
                    $object->setIsActivated(false);
                } else {
                    $object->setIsActivated(true);
                }

                if ($object->getIsActivated()) {
                    $expiresDays = (int)$this->config->getGeneralExpiresAfterDays();
                    if ($expiresDays && !$object->getExpiresAt()) {
                        $date = $this->date->gmtDate(
                            'Y-m-d h:i', $this->date->gmtTimestamp() + $expiresDays * 24 * 60 * 60
                        );
                        $object->setExpiresAt($date);
                    }
                }
            } else { // spending always active
                $object->setIsActivated(true);
            }
        } else {
            $expiresDays = (int)$this->config->getGeneralExpiresAfterDays();
            if ($expiresDays && $object->getIsActivated() && !$object->getExpiresAt()) {
                $date = $this->date->gmtDate(
                    'Y-m-d h:i', $this->date->gmtTimestamp() + $expiresDays * 24 * 60 * 60
                );
                $object->setExpiresAt($date);
            }
        }
        $object->setUpdatedAt((new \DateTime( "now", new \DateTimeZone( "UTC" ) ))->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT));

        return parent::_beforeSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var  \Mirasvit\Rewards\Model\Transaction $object */
        if (!$object->getIsMassStatus()) {
        }

        return parent::_afterSave($object);
    }

    /************************/
}
