<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Wallet
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Wallet\Controller\Adminhtml\Index;

class Compare extends \Magento\Customer\Controller\Adminhtml\Index
{
	/**
	 * Customer compare grid
	 *
	 * @return \Magento\Framework\View\Result\Layout
	 */
	public function execute()
	{
		$this->initCurrentCustomer();
		$resultLayout = $this->resultLayoutFactory->create();
		return $resultLayout;
	}
}