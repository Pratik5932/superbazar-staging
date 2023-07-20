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



namespace Mirasvit\Rewards\Service;

use Magento\Customer\Model\CustomerFactory;
use Mirasvit\Rewards\Model\Config;

class Widget
{
    private $customerFactory;

    private $config;

    public function __construct(
        CustomerFactory $customerFactory,
        Config $config
    ) {
        $this->customerFactory = $customerFactory;
        $this->config          = $config;
    }

    /**
     * @return string
     */
    public function getAddThisWidgetCode()
    {
        return $this->config->getAddThisWidgetCode();
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     *
     * @return \Magento\Customer\Model\Customer
     */
    protected function getCustomer($customer)
    {
        return $this->customerFactory->create()->load($customer->getId());
    }
}
