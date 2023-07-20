<?php
/**
 * @category   Webkul
 * @package    Webkul_SocialSignup
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\SocialSignup\Model;

use Webkul\SocialSignup\Api\Data\FacebooksignupInterface;
use Webkul\SocialSignup\Model\ResourceModel\Facebooksignup\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class FacebooksignupRepository implements \Webkul\SocialSignup\Api\FacebooksignupRepositoryInterface
{
    /**
     * @var FacebooksignupFactory
     */
    protected $_facebooksignupFactory;

    /**
     * @var \Webkul\SocialSignup\Model\ResourceModel\SocialSignup\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Webkul\SocialSignup\Model\ResourceModel\SocialSignup
     */
    protected $_resourceModel;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $_extensibleDataObjectConverter;

    /**
     * @param FacebooksignupFactory                                                     $facebooksignupFactory
     * @param \Webkul\SocialSignup\Model\ResourceModel\Facebooksignup\CollectionFactory $collectionFactory
     * @param \Webkul\SocialSignup\Model\ResourceModel\Facebooksignup                   $resourceModel
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter                      $extensibleDataObjectConverter
     */
    public function __construct(
        FacebooksignupFactory $facebooksignupFactory,
        \Webkul\SocialSignup\Model\ResourceModel\Facebooksignup\CollectionFactory $collectionFactory,
        \Webkul\SocialSignup\Model\ResourceModel\Facebooksignup $resourceModel,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
    
        $this->_resourceModel = $resourceModel;
        $this->_facebooksignupFactory = $facebooksignupFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }
    /**
     * get customer collection by customer id
     * @param  integer $customerId customer id
     * @return object
     */
    public function getByCustomerId($customerId)
    {
        $collection = $this->_collectionFactory->create()
                        ->addFieldToFilter('customer_id', ['eq'=>$customerId]);
        return $collection;
    }
    /**
     * get customer collection by facebook id
     * @param  integer $fbId facebook id of customer
     * @return object
     */
    public function getByFbId($fbId)
    {
        $collection = $this->_collectionFactory->create()
                        ->addFieldToFilter('fb_id', ['eq'=>$fbId]);
        return $collection;
    }
    /**
     * get customer collection by entity id
     * @param  integer $entity_id entity_id of customer
     * @return collection
     */
    public function getById($entityId)
    {
        $collection = $this->_facebooksignupFactory->create()->load($entityId);
        return $collection;
    }
}
