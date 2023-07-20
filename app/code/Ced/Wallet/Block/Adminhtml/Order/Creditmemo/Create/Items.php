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
namespace Ced\Wallet\Block\Adminhtml\Order\Creditmemo\Create;

/**
 * Adminhtml credit memo items grid
 */
class Items extends \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items
{
	/**
	 * Prepare child blocks
	 *
	 * @return $this
	 */
	protected function _prepareLayout()
	{

		$onclick = "submitAndReloadArea($('creditmemo_item_container'),'" . $this->getUpdateUrl() . "')";
		$this->addChild(
				'update_button',
				'Magento\Backend\Block\Widget\Button',
				['label' => __('Update Qty\'s'), 'class' => 'update-button', 'onclick' => $onclick]
		);
	
		if ($this->getCreditmemo()->canRefund()) {
			if ($this->getCreditmemo()->getInvoice() && $this->getCreditmemo()->getInvoice()->getTransactionId()) {
				$this->addChild(
						'submit_button',
						'Magento\Backend\Block\Widget\Button',
						[
						'label' => __('Refund'),
						'class' => 'save submit-button refund primary',
						'onclick' => 'disableElements(\'submit-button\');submitCreditMemo()'
						]
				);
			}
			$this->addChild(
					'submit_offline',
					'Magento\Backend\Block\Widget\Button',
					[
					'label' => __('Refund Offline'),
					'class' => 'save submit-button primary',
					'onclick' => 'disableElements(\'submit-button\');submitCreditMemoOffline()'
					]
			);
			$this->addChild(
					'submit_wallet',
					'Magento\Backend\Block\Widget\Button',
					[
					'label' => __('Pay To Wallet'),
					'class' => 'save submit-button primary',
					'onclick' => 'payWallet()',
					]
			);
		} else {
			$this->addChild(
					'submit_button',
					'Magento\Backend\Block\Widget\Button',
					[
					'label' => __('Refund Offline'),
					'class' => 'save submit-button primary',
					'onclick' => 'disableElements(\'submit-button\');submitCreditMemoOffline()'
					]
			);
	
			$this->addChild(
					'submit_wallet',
					'Magento\Backend\Block\Widget\Button',
					[
					'label' => __('Pay To Wallet'),
					'class' => 'save submit-button primary',
					'onclick' => 'payWallet()',
					]
			);
		}
	
	}
	
	
}