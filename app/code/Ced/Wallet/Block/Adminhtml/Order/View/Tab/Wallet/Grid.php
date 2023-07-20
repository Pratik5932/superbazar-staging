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

namespace Ced\Wallet\Block\Adminhtml\Order\View\Tab\Wallet;

/**
 * Adminhtml newsletter queue grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry|null
     */
	
	protected $_transactionFactory;
	
    protected $_coreRegistry = null;
    
    public $_objectManager;



    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Newsletter\Model\ResourceModel\Queue\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\Wallet\Model\TransactionFactory $transactionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
    	$this->_transactionFactory = $transactionFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_objectManager=$objectManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('walletGrid');
        $this->setDefaultSort('start_at');
        $this->setDefaultDir('desc');
		
        $this->setUseAjax(true);

    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('wallet/wallet/ordergrid', ['_current' => true]);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
        $incId = $order->getIncrementId();
    	$collection = $this->_transactionFactory->create()->getCollection()->addFieldToFilter('order_id',$incId);;
    	$this->setCollection($collection);
    	
    	parent::_prepareCollection();
    	return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id',
				array(
						'header' => 'ID',
						'align' =>'right',
						'width' => '50px',
						'index' => 'id',
				));
		$this->addColumn('order_id',
				array(
						'header' => 'Order ID',
						'align' =>'left',
						'index' => 'order_id',
				));
		$this->addColumn('action', array(
				'header' => 'Action',
				'type' =>'options',
				'align' =>'left',
				'index' => 'action',
				'options' =>['0'=>'Credit','1'=>'Debit'],
			//	'renderer' => 'Ced\Wallet\Block\Adminhtml\Order\View\Tab\Wallet\Renderer\Action',
		));
		$this->addColumn('amount', array(
				'header' => 'Amount',
				'align' =>'left',
				'index' => 'amount',
		));
		$this->addColumn('created_at', array(
				'header' => 'Time',
				'align' =>'left',
				'index' => 'created_at',
		));
		return parent::_prepareColumns();
    }
}
