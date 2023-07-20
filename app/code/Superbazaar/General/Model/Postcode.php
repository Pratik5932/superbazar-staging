<?php
namespace Superbazaar\General\Model;
class Postcode implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $options = [];
        $options[] = ['label' => '3024', 'value' => '3024'];
        $options[] = ['label' => '3030', 'value' => '3030'];
        $options[] = ['label' => '3064', 'value' => '3064'];
        return $options;
    }
}