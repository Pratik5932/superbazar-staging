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



namespace Mirasvit\Rewards\Api\Service;

interface RefundServiceInterface
{
    /**
     * @param int $orderId
     * @return \Mirasvit\Rewards\Api\Data\Refund\RefundInfoInterface
     */
    public function getByOrderId($orderId);

    /**
     * @param int $creditMemoId
     * @return \Mirasvit\Rewards\Api\Data\Refund\RefundInfoInterface
     */
    public function getByCreditMemoId($creditMemoId);
}
