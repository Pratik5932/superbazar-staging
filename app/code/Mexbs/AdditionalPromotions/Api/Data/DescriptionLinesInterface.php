<?php
namespace Mexbs\AdditionalPromotions\Api\Data;

/**
 * Interface DiscountDetailsInterface
 * @api
 */
interface DescriptionLinesInterface
{
    /**
     * Get description lines
     *
     * @return string
     */
    public function getLine();

    /**
     * @param string $line
     * @return $this
     */
    public function setLine($line);
}
