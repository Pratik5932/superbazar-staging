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

namespace Bss\OrderDeliveryDate\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class CountryColumn extends Select
{
    private $countryHelper;

    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function setInputName($value)
    {
        #echo $value;exit;
        return $this->setName($value . '[]');
    }

    public function _toHtml(): string
    {
#echo "adas";exit;
    if (!$this->getOptions()) {
    $this->setOptions($this->getSourceOptions());
}
$this->setExtraParams('multiple="multiple"');
return parent::_toHtml();


}
private function getSourceOptions(): array
{
    return [
        ['value' => '0','label' => 'Sunday'],
        ['value' => '1','label' => 'Monday'],
        ['value' => '2','label' => 'Tuesday'],
        ['value' => '3','label' => 'Wednesday'],
        ['value' => '4','label' => 'Thursday'],
        ['value' => '5','label' => 'Friday'],
        ['value' => '6','label' => 'Saturday'],
    ];
}
}