<?php
namespace Magecomp\Paymentfee\Api;

/**
 * Interface PaymentfeeInterface
 * Magecomp\Paymentfee\Api
 */
interface PaymentfeeInterface
{
    /**
     * Add Payment Fee
     *
     *  @param int $quoteid
     *  @param int $storeid
     *  @return string
     */
    public function addPaymentFee(
        $quoteid,$storeid
    );

}
