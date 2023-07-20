<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ced\Wallet\Plugin;
/**
 * Class DataProviderWithDefaultAddresses
 */
class DataProviderWithDefaultAddresses
{
    public function afterGetData($subject, $result) {
        foreach($result as $customer){
            $result[$customer['customer']['entity_id']]['customer']['enable_wallet_system'] = isset($result[$customer['customer']['entity_id']]['customer']['enable_wallet_system']) ? intval($result[$customer['customer']['entity_id']]['customer']['enable_wallet_system']) : 1;
        }
        return $result;
    }
}
