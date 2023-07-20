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

use Magento\Framework\DataObject\IdentityInterface;
use Mirasvit\Rewards\Api\Data\TransactionInterface;

/**
 * @method \Mirasvit\Rewards\Model\ResourceModel\Transaction\Collection getCollection()
 * @method \Mirasvit\Rewards\Model\Transaction load(int $id)
 * @method bool getIsMassDelete()
 * @method \Mirasvit\Rewards\Model\Transaction setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method \Mirasvit\Rewards\Model\Transaction setIsMassStatus(bool $flag)
 * @method bool getIsAllowPending()
 * @method \Mirasvit\Rewards\Model\Transaction setIsAllowPending(bool $flag)
 * @method \Mirasvit\Rewards\Model\ResourceModel\Transaction getResource()
 */
class Transaction extends \Magento\Framework\Model\AbstractModel implements TransactionInterface
{
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_DISCARDED = 2;

    /**
     * @var string
     */
    protected $_eventPrefix = 'rewards_transaction';

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\Model\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb
     */
    protected $resourceCollection;

    /**
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface             $localeDate
     * @param \Magento\Framework\Model\Context                                 $context
     * @param \Magento\Framework\Registry                                      $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource          $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb                    $resourceCollection
     * @param array                                                            $data
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->localeDate = $localeDate;
        $this->context = $context;
        $this->registry = $registry;
        $this->resource = $resource;
        $this->resourceCollection = $resourceCollection;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rewards\Model\ResourceModel\Transaction');
    }

    /**
     * @param bool|false $emptyOption
     * @return array
     */
    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    /**
     * @var \Magento\Customer\Model\Customer|\Magento\Framework\DataObject|false
     */
    protected $_customer = null;

    /**
     * @return bool|\Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        if ($this->_customer === null) {
            if ($this->getCustomerId()) {
                $this->_customer = $this->customerCollectionFactory->create()
                    ->addAttributeToSelect('*')
                    ->addFieldToFilter('entity_id', $this->getCustomerId())
                    ->getFirstItem();
            } else {
                $this->_customer = false;
            }
        }

        return $this->_customer;
    }

    /************************/

    /**
     * @return string
     */
    public function getExpiresAtFormatted()
    {
        $expires = $this->getData('expires_at');
        $date = date_create($expires);
        $dateNow = date_create();
        $diff = date_diff($date, $dateNow);
        if ($expires) {
            return $this->formatDate($diff);
        } else {
            return '-';
        }
    }

    /**
     * @return string
     */
    public function getCreatedAtFormatted()
    {
        $expires = $this->getData('created_at');
        if (!$expires) {
            return '-';
        }

        $date    = date_create($expires);
        $dateNow = date_create();
        $diff    = date_diff($date, $dateNow);

        return $this->formatDate($diff);
    }

    /**
     * @return string
     */
    public function getActivatedAtFormatted()
    {
        $expires = $this->getData(self::KEY_ACTIVATED_AT);
        $date = date_create($expires);
        $dateNow = date_create();
        $diff = date_diff($date, $dateNow);
        if ($expires) {
            return $this->formatDate($diff);
        } else {
            return '-';
        }
    }

    /**
     * @param \DateInterval $diff
     * @return string
     */
    private function formatDate($diff)
    {
        if (!$diff) {
            return '';
        }
        if ($diff->days === 0) {
            return __('today');
        }
        $k = $diff->days % 10;
        $exp = [0, 11, 12, 13];
        if ($diff->invert) {
            if ($diff->days === 1) {
                return __('in 1 day');
            }
            if ($k > 1 && $k <= 4) {
                $string = __('in %1 %2', $diff->days, 'days');
            } elseif ($k > 4 || in_array($k, $exp)) {
                $string = __('in %1 days', $diff->days);
            } else {
                $string = __('in %1 days', $diff->days);
            }
        } else {
            if ($diff->days === 1) {
                return __('1 day ago');
            }
            if ($k > 1 && $k <= 4) {
                $string = __('%1 %2 ago', $diff->days, 'days');
            } elseif ($k > 4 || in_array($k, $exp)) {
                $string = __('%1 days ago', $diff->days);
            } else {
                $string = __('%1 days ago', $diff->days);
            }
        }

        return $string->render();
    }

    /**
     * @return int
     */
    public function getDaysLeft()
    {
        if ($expires = $this->getData('expires_at')) {
            $diff = strtotime((string) $expires) - time();
            $days = (int) ($diff / 60 / 60 / 24);

            return $days;
        }
        return 0;
    }


    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getData(self::KEY_CUSTOMER_ID);
    }

    /**
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::KEY_CUSTOMER_ID, $customerId);
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->getData(self::KEY_AMOUNT);
    }

    /**
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        return $this->setData(self::KEY_AMOUNT, $amount);
    }

    /**
     * @return float
     */
    public function getAmountUsed()
    {
        return $this->getData(self::KEY_AMOUNT_USED);
    }

    /**
     * @param float $amountUsed
     * @return $this
     */
    public function setAmountUsed($amountUsed)
    {
        return $this->setData(self::KEY_AMOUNT_USED, $amountUsed);
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->getData(self::KEY_COMMENT);
    }

    /**
     * @param string $comment
     * @return $this
     */
    public function setComment($comment)
    {
        return $this->setData(self::KEY_COMMENT, $comment);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->getData(self::KEY_CODE);
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        return $this->setData(self::KEY_CODE, $code);
    }

    /**
     * @return int
     */
    public function getIsExpired()
    {
        return $this->getData(self::KEY_IS_EXPIRED);
    }

    /**
     * @param int $isExpired
     * @return $this
     */
    public function setIsExpired($isExpired)
    {
        return $this->setData(self::KEY_IS_EXPIRED, $isExpired);
    }

    /**
     * @return int
     */
    public function getIsExpirationEmailSent()
    {
        return $this->getData(self::KEY_IS_EXPIRATION_EMAIL_SENT);
    }

    /**
     * @param int $isExpirationEmailSent
     * @return $this
     */
    public function setIsExpirationEmailSent($isExpirationEmailSent)
    {
        return $this->setData(self::KEY_IS_EXPIRATION_EMAIL_SENT, $isExpirationEmailSent);
    }

    /**
     * @return string
     */
    public function getExpiresAt()
    {
        return $this->getData(self::KEY_EXPIRES_AT);
    }

    /**
     * @param string $expiresAt
     * @return $this
     */
    public function setExpiresAt($expiresAt)
    {
        return $this->setData(self::KEY_EXPIRES_AT, $expiresAt);
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::KEY_CREATED_AT);
    }

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::KEY_CREATED_AT, $createdAt);
    }

    /**
     * @return string
     */
    public function getActivatedAt()
    {
        return $this->getData(self::KEY_ACTIVATED_AT);
    }

    /**
     * @param string $activatedAt
     * @return $this
     */
    public function setActivatedAt($activatedAt)
    {
        return $this->setData(self::KEY_ACTIVATED_AT, $activatedAt);
    }

    /**
     * @return bool
     */
    public function getIsActivated()
    {
        return $this->getData(self::KEY_IS_ACTIVATED);
    }

    /**
     * @param bool $isActivated
     * @return $this
     */
    public function setIsActivated($isActivated)
    {
        return $this->setData(self::KEY_IS_ACTIVATED, $isActivated);
    }
}
