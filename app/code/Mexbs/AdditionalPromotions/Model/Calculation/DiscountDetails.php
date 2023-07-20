<?php
namespace Mexbs\AdditionalPromotions\Model\Calculation;

use Mexbs\AdditionalPromotions\Api\Data\DiscountDetailsInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class DiscountDetails extends AbstractSimpleObject implements DiscountDetailsInterface
{
    const COUPON_CODE = 'coupon_code';
    const DESCRIPTION_LINES = 'description_lines';


    public function getCouponCode()
    {
        return $this->_get(self::COUPON_CODE);
    }

    public function setCouponCode($couponCode)
    {
        return $this->setData(self::COUPON_CODE, $couponCode);
    }

    public function getDescriptionLines()
    {
        return $this->_get(self::DESCRIPTION_LINES);
    }

    public function setDescriptionLines($descriptionLines)
    {
        return $this->setData(self::DESCRIPTION_LINES, $descriptionLines);
    }
}
