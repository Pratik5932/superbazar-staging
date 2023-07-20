<?php
namespace Mexbs\AdditionalPromotions\Model\Calculation;

use Mexbs\AdditionalPromotions\Api\Data\DescriptionLinesInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class DescriptionLines extends AbstractSimpleObject implements DescriptionLinesInterface
{
    const LINE = 'line';


    public function getLine()
    {
        return $this->_get(self::LINE);
    }

    public function setLine($line)
    {
        return $this->setData(self::LINE, $line);
    }
}
