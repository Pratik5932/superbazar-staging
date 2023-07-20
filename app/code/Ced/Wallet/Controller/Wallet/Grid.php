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
namespace Ced\Wallet\Controller\Wallet;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
class Grid extends \Magento\Framework\App\Action\Action
{
	/**
	 * @var PageFactory
	 */
	protected $resultPageFactory;
	/**
	 * @var \Magento\Framework\Data\Form\FormKey
	 */
	protected $formKey;
	/**
	 * @param Context $context
	 * @param PageFactory $resultPageFactory
	 */
	public function __construct(
			Context $context,
			PageFactory $resultPageFactory,
			\Ced\Wallet\Helper\Data $helper
	) {

		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
		$this->helper = $helper;
	}
	/**
	 *
	 * @return \Magento\Framework\View\Result\Page
	 */
	public function execute()
	{
	   $isEnabled = $this->helper->isModuleEnabled();
        if (!$isEnabled) {
            $this->_redirect("*/*");
        }

		$resultPage = $this->resultPageFactory->create();
	    return $resultPage;
	}
}