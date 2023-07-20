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


namespace Mirasvit\RewardsAdminUi\Reports;

use Mirasvit\Report\Model\AbstractReport;

class Overview extends AbstractReport
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('Reward Points');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'rewards_overview';
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setTable('mst_rewards_points_aggregated_hour');

        $this->setPrimaryFilters(
            [
            'mst_rewards_points_aggregated_hour|period',
            'mst_rewards_points_aggregated_hour|customer_group_id',
            'mst_rewards_points_aggregated_hour|store_id',
        ]
        );
        $this->setColumns([
            'mst_rewards_points_aggregated_hour|order_earn__sum',
            'mst_rewards_points_aggregated_hour|inactivity__sum',
            'mst_rewards_points_aggregated_hour|send_link__sum',
            'mst_rewards_points_aggregated_hour|order_earn_cancel__sum',
            'mst_rewards_points_aggregated_hour|order_refund__sum',
            'mst_rewards_points_aggregated_hour|order_spend__sum',
            'mst_rewards_points_aggregated_hour|order_spend_restore__sum',
            'mst_rewards_points_aggregated_hour|expired_points__sum',
            'mst_rewards_points_aggregated_hour|total_points_spent_in_money__sum',
            'mst_rewards_points_aggregated_hour|admin_transaction__sum',
            'mst_rewards_points_aggregated_hour|facebook_like__sum',
            'mst_rewards_points_aggregated_hour|pinterest_pin__sum',
            'mst_rewards_points_aggregated_hour|twitter_tweet__sum',
            'mst_rewards_points_aggregated_hour|create_rma__sum',
            'mst_rewards_points_aggregated_hour|signup__sum',
        ]);

        $this->setDimensions(['mst_rewards_points_aggregated_hour|period__day']);

        $this->setPrimaryDimensions([
            'mst_rewards_points_aggregated_hour|period__hour',
            'mst_rewards_points_aggregated_hour|period__day',
            'mst_rewards_points_aggregated_hour|period__week',
            'mst_rewards_points_aggregated_hour|period__month',
            'mst_rewards_points_aggregated_hour|period__year',
        ]);

        $this->getChartConfig()
            ->setType('column')
            ->setDefaultColumns([
                'mst_rewards_points_aggregated_hour|order_earn__sum',
            ]);
    }

    /**
     * @return mixed|string[]|null
     */
    public function getApplicableColumns()
    {
        return $this->getColumns();
    }

    /**
     * @return mixed|string[]|null
     */
    public function getApplicableDimensions()
    {
        return $this->getPrimaryDimensions();
    }
}
