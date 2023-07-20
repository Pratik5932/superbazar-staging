<?php

namespace Magecomp\Paymentfee\Model\System;

use Magento\Shipping\Model\Source\HandlingType;
class HandlingTypes
{
    protected $handling;

    const HANDLING_TYPE_COMBINED = 'C';
    const HANDLING_TYPE_MIN = 'M';

    public function __construct(HandlingType $HandlingType)
    {
        $this->handling = $HandlingType;

    }

    public function toOptionArray()
    {
        $returnArray = $this->handling->toOptionArray();
        $returnArray[] = ['value'=>self::HANDLING_TYPE_COMBINED, 'label'=>__('Combined')];
        $returnArray[] = ['value'=>self::HANDLING_TYPE_MIN, 'label'=>__('Fixed Minimum')];
        return $returnArray;
    }
}