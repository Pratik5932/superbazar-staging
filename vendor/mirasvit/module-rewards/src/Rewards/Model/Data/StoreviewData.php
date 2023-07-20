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


namespace Mirasvit\Rewards\Model\Data;

use Magento\Framework\Api\AbstractSimpleObject;
use Mirasvit\Rewards\Api\Data\StoreviewDataInterface;

class StoreviewData extends AbstractSimpleObject implements StoreviewDataInterface
{
    /**
     * @inheritDoc
     */
    public function getStoreviewId()
    {
        return $this->_get(self::KEY_STOREVIEW_ID);
    }

    /**
     * @inheritDoc
     */
    public function setStoreviewId($id)
    {
        return $this->setData(self::KEY_STOREVIEW_ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->_get(self::KEY_VALUE);
    }

    /**
     * @inheritDoc
     */
    public function setValue($value)
    {
        return $this->setData(self::KEY_VALUE, $value);
    }
}