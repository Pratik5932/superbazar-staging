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



namespace Mirasvit\Rewards\Api\Repository;


interface RefundRepositoryInterface
{
    /**
     * @param \Mirasvit\Rewards\Api\Data\RefundInterface $refund
     * @return \Mirasvit\Rewards\Api\Data\RefundInterface
     * @throws \Exception
     */
    public function save(\Mirasvit\Rewards\Api\Data\RefundInterface $refund);

    /**
     * @param int $refundId
     * @return \Mirasvit\Rewards\Api\Data\RefundInterface
     */
    public function get($refundId);

    /**
     * @param int $creditmemoId
     * @return \Mirasvit\Rewards\Api\Data\RefundInterface
     */
    public function getByCreditmemoId($creditmemoId);

    /**
     * @param \Mirasvit\Rewards\Api\Data\RefundInterface $refund
     * @return bool Will returned True if deleted
     * @throws \Exception
     */
    public function delete(\Mirasvit\Rewards\Api\Data\RefundInterface $refund);
}