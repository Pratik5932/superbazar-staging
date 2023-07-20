<?php
 /**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model\Config\Backend;
 
class Walkthrough extends \Magento\Framework\App\Config\Value
{
    public function beforeSave()
    {
        $label = $this->getData('field_config/label');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create(\Webkul\MobikulCore\Helper\Data::class);
        $previousValue = $helper->getConfigData("mobikul/walkthrough/walkthrough_version");

        if ($this->getValue() == '') {
            throw new \Magento\Framework\Exception\ValidatorException(__($label . ' is required.'));
        } else if (!is_numeric($this->getValue())) {
            throw new \Magento\Framework\Exception\ValidatorException(__($label . ' is not a number.'));
        } else if ($this->getValue() < 0) {
            throw new \Magento\Framework\Exception\ValidatorException(__($label . ' is less than 0.'));
        } else if ($this->getValue() < $previousValue) {
            throw new \Magento\Framework\Exception\ValidatorException(__($label . ' can not be less than previous.'));
        }

        $this->setValue($this->getValue());

        parent::beforeSave();
    }
}