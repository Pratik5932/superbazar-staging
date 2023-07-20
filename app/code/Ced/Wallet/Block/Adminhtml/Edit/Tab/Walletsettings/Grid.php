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
namespace Ced\Wallet\Block\Adminhtml\Edit\Tab\Walletsettings;

/**
 * Adminhtml newsletter queue grid block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
use Magento\Customer\Controller\RegistryConstants;
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry|null
     */
	
	protected $_transactionFactory;
	
    protected $_coreRegistry = null;



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
        \Ced\Wallet\Model\TransactionFactory $transactionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
    	$this->_transactionFactory = $transactionFactory;
        $this->_coreRegistry = $coreRegistry;
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
        return $this->getUrl('wallet/wallet/grid', ['_current' => true]);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
       $cid = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
       if(!$cid){
       	$cid = $this->getRequest()->getParams('id'); 
       }
    	$collection = $this->_transactionFactory->create()->getCollection()->addFieldToFilter('customer_id',$cid);
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
				'align' =>'left',
				'index' => 'action',
				'renderer' => 'Ced\Wallet\Block\Adminhtml\Edit\Tab\Walletsettings\Renderer\Action',
		));
		$this->addColumn('amount', array(
				'header' => 'Amount',
				'align' =>'left',
				'index' => 'amount',
				'class' => 'validate-greater-than-zero'
		));
		$this->addColumn('created_at', array(
				'header' => 'Time',
				'align' =>'left',
				'index' => 'created_at',
		));
		return parent::_prepareColumns();
    }
    
    
}
