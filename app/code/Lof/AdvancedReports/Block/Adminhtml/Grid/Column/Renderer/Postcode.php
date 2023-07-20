<?php
/**
* Landofcoder
* 
* NOTICE OF LICENSE
* 
* This source file is subject to the Landofcoder.com license that is
* available through the world-wide-web at this URL:
* http://landofcoder.com/license
* 
* DISCLAIMER
* 
* Do not edit or add to this file if you wish to upgrade this extension to newer
* version in the future.
* 
* @category   Landofcoder
* @package    Lof_AdvancedReports
* @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
* @license    http://www.landofcoder.com/LICENSE-1.0.html
*/
namespace Lof\AdvancedReports\Block\Adminhtml\Grid\Column\Renderer; 

use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;

/**
* Adminhtml grid item renderer date
*/
class Postcode extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
    * Renders grid column
    *
    * @param   Varien_Object $row
    * @return  string
    */
    public function render(\Magento\Framework\DataObject $row)
    {
        $customeEmail = $row->getData('email');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer = $objectManager->create('Magento\Customer\Model\Customer')->loadByEmail($customeEmail);
        if ($customer) {
            $shippingAddress = $customer->getDefaultShippingAddress();
            if ($shippingAddress) {
                return $shippingAddress->getPostcode();
            }
        }
        else {
            return __("--");
        }

    }

}

