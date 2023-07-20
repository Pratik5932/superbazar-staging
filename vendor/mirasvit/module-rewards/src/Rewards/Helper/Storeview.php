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



namespace Mirasvit\Rewards\Helper;

class Storeview extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Json
     */
    private $jsonHelper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $context;

    public function __construct(
        Json $jsonHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->jsonHelper   = $jsonHelper;
        $this->storeManager = $storeManager;
        $this->context      = $context;

        parent::__construct($context);
    }

    /**
     * @param mixed $object
     * @param string                        $field
     * @param string|int                    $value
     *
     * @return void
     */
    public function setStoreViewValue($object, $field, $value)
    {
        $storeId = (int) $object->getStoreId();
        $serializedValue = $object->getData($field);
        $arr = (array)$this->jsonHelper->unserialize($serializedValue);

        if ($storeId === 0) {
            $arr[0] = $value;
        } else {
            $arr[$storeId] = $value;
            if (!isset($arr[0])) {
                $arr[0] = $value;
            }
        }
        $object->setData($field, $this->jsonHelper->serialize($arr));
    }

    /**
     * @param mixed $object
     * @param string                        $field
     * @return null
     */
    public function getStoreViewValue($object, $field)
    {
        /** @var int|null $storeId */
        $storeId = $object->getStoreId();
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $serializedValue = $object->getData($field);
        $arr = $this->jsonHelper->unserialize($serializedValue);
        $defaultValue = null;
        if (isset($arr[0])) {
            $defaultValue = $arr[0];
        }

        if (isset($arr[$storeId])) {
            $localizedValue = $arr[$storeId];
        } else {
            $localizedValue = $defaultValue;
        }

        return $localizedValue;
    }
}
