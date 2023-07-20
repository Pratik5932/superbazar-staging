<?php
/**
* BSS Commerce Co.
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://bsscommerce.com/Bss-Commerce-License.txt
*
* @category   BSS
* @package    Bss_OrderDeliveryDate
* @author     Extension Team
* @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/

namespace Bss\OrderDeliveryDate\Model\Config\Backend;

class Serialized extends \Magento\Framework\App\Config\Value
{
    /**
    * @var \Magento\Framework\Serialize\Serializer\Serialize
    */
    protected $serialize;

    /**
    * Serialized constructor.
    * @param \Magento\Framework\Model\Context $context
    * @param \Magento\Framework\Registry $registry
    * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
    * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
    * @param \Magento\Framework\Serialize\Serializer\Serialize $serialize
    * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
    * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
    * @param array $data
    */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->serialize = $serialize;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
    * {@inheritdoc}
    *
    * @return void
    */
    protected function _afterLoad()
    {

        if (!is_array($this->getValue())) {
            $value = $this->getValue();
            $this->setValue(empty($value) ? false : $this->serialize->unserialize($value));
        }
        /* if (!is_array($this->getValue())) {
        $value = $this->getValue();
        $time_slot_arr = $this->serialize->unserialize($value);
        $time_slot_arr1 = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $sellerPostcode = $objectManager->get('Magento\Backend\Model\Auth\Session')->getUser()->getPostCode();
        #echo $sellerPostcode;exit;
        /*foreach ($time_slot_arr as $key =>$data) {
        if (isset($data['name']) && $data['name'] != $sellerPostcode) {
        unset($time_slot_arr[$key]);
        $ser = $this->serialize->serialize($time_slot_arr);
        $this->setValue(empty($ser) ? false : $this->serialize->unserialize($ser));
        }else{
        $ser = $this->serialize->serialize($time_slot_arr);
        $this->setValue(empty($ser) ? false : $this->serialize->unserialize($ser));
        }
        }


        }*/
    }

    /**
    * {@inheritdoc}
    *
    * @return \Magento\Framework\App\Config\Value
    */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            unset($value['__empty']);
        }

        $field = $this->getData('field');
        if ($field == 'time_slots') {
            foreach ($value as $data) {

                if (isset($data['price']) && !empty($data['price']) && !is_numeric($data['price'])) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Time Slots Price must be a number'));
                }
            }
        }


        $this->setValue($value);

        if (is_array($this->getValue())) {
            $this->setValue($this->serialize->serialize($this->getValue()));
        }
        return parent::beforeSave();
    }
}
