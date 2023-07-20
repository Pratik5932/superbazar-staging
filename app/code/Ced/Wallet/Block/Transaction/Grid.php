<?php
namespace Ced\Wallet\Block\Transaction;
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
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    protected $_gridFactory;
    protected $eavConfig;
    protected $_storesFactory;

    protected $_status;
    public $_objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Eav\Model\Config $eavConfig,
        \Ced\Wallet\Model\TransactionFactory $gridFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Customer $Customer,
        array $data = []
    )
    {
        $this->_objectManager = $objectManager;
        $this->eavConfig = $eavConfig;
        $this->_gridFactory = $gridFactory;
        $this->session = $customerSession;
        $this->Customer = $Customer;
        parent::__construct($context, $backendHelper, $data);
        $this->setData('area', 'adminhtml');
        //$this->_prepareCollection();
    }


    protected function _construct()
    {

       
        parent::_construct();
        $this->setId('transactionGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
      //  $this->setVarNameFilter('post_filter');


    }

/*    protected function _filterStoreCondition($collection, \Magento\Framework\DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }
*/
    protected function _prepareCollection()
    {

        $customerId = $this->_objectManager->create('Magento\Customer\Model\Session')->getCustomer()->getId();
        $collection = $this->_gridFactory->create()->getCollection()->addFieldToFilter('customer_id',$customerId);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
    	$storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
    	$currencyCode = $storeManager->getStore()->getCurrentCurrencyCode();
        $this->addColumn(
            'id',
            [
                'header' => __('ID #'),
                'index' => 'id',

            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created At'),
                'type' => 'date',
                'index' => 'created_at',

            ]
        );




        $this->addColumn(
            'order_id',
            [
                'header' => __('Order Id/Wallet-Status'),
                'index' => 'order_id',

            ]
        );
        $transactionModel = $this->_gridFactory->create();
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'options',
                'options' => $transactionModel->getTransactionActions(),
                'index' => 'action',

            ]
        );

        $this->addColumn(
            'amount',
            [
                'header' => __('Amount'),
                'type' => 'currency',
                'index' => 'amount',
        		'currency_code' =>$currencyCode

            ]
        );
        $this->addColumn(
            'comment',
            [
                'header' => __('Comment'),
                'type' => 'text',
                'index' => 'comment',

            ]
        );
          $this->addColumn(
            'expiration_time',
            [
                'header' => __('Cashback Expiration'),
                'type' => 'date',
                'index' => 'expiration_time',

            ]
        );
        $this->addColumn(
            'transaction_with',
            [
                'header' => __('Transactioned With'),
                'type' => 'text',
                'index' => 'transaction_with',

            ]
        );
      

        return parent::_prepareColumns();

    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
       
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    protected function _prepareFilterButtons()
    {
        $this->setChild(
            'reset_filter_button',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'label' => __('Reset Filter'),
                    'onclick' => $this->getJsObjectName() . '.resetFilter()',
                    'class' => 'action-reset action-tertiary',
                    'area' => 'adminhtml'
                ]
            )->setDataAttribute(['action' => 'grid-filter-reset'])
        );
        $this->setChild(
            'search_button',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'label' => __('Search'),
                    'onclick' => $this->getJsObjectName() . '.doFilter()',
                    'class' => 'action-secondary',
                    'area' => 'adminhtml'
                ]
            )->setDataAttribute(['action' => 'grid-filter-apply'])
        );
    }

}