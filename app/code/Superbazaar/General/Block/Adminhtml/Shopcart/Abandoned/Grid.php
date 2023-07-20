<?php
/**
* Copyright © Magento, Inc. All rights reserved.
* See COPYING.txt for license details.
*/
namespace Superbazaar\General\Block\Adminhtml\Shopcart\Abandoned;

/**
* Adminhtml abandoned shopping carts report grid block
*
* @method \Magento\Reports\Model\ResourceModel\Quote\Collection getCollection()
*
* @author      Magento Core Team <core@magentocommerce.com>
* @SuppressWarnings(PHPMD.DepthOfInheritance)
*/
class Grid extends \Magento\Reports\Block\Adminhtml\Grid\Shopcart
{
    /**
    * @var \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory
    */

    /**
    * Grid constructor
    *
    * @return void
    */


    /**
    * Prepare collection
    *
    * @return \Magento\Backend\Block\Widget\Grid
    */

    /**
    * Prepare columns
    *
    * @return \Magento\Backend\Block\Widget\Grid\Extended
    * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
    */

    protected $_quotesFactory;
    protected $sellerFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory $quotesFactory,
        \Webkul\Marketplace\Model\Seller $sellerFactory,
        array $data = []
    ) {
        $this->_quotesFactory = $quotesFactory;
        $this->sellerFactory = $sellerFactory;
        parent::__construct($context, $backendHelper, $data);
    }
    protected function _addColumnFilterToCollection($column)
    {
        $field = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
        $skip = ['subtotal', 'customer_name', 'email'];

        if (in_array($field, $skip)) {
            return $this;
        }

        parent::_addColumnFilterToCollection($column);
        return $this;
    }
    protected function _prepareCollection()
    {
        $collection = $this->_quotesFactory->create();


        $filter = $this->getParam($this->getVarNameFilter(), []);
        if ($filter) {
            $filter = base64_decode($filter);
            parse_str(urldecode($filter), $data);
        }
        if (!empty($data)) {
            $collection->prepareForAbandonedReport($this->_storeIds, $data);
        } else {
            $collection->prepareForAbandonedReport($this->_storeIds);
        }

        $this->sales_order_table = "main_table";
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $isSeller = $objectManager->get("Webkul\Marketplace\Helper\Data")->isSeller();
        $currentAdminUser = $objectManager->get('Magento\Backend\Model\Auth\Session')->getUser();
        $extensionUser = $currentAdminUser->getPostCode();

        /* $sellerModel = $this->sellerFactory->getCollection()->addFieldToFilter('seller_id', $currentAdminUser->getId());
        $sellerStatus = 0;
        //if ($sellerModel->getSize()) {
        foreach ( $sellerModel as $value) {
        if ($value->getIsSeller() == 1) {
        $sellerStatus = $value->getIsSeller();
        }
        }
        // }
        #echo $sellerStatus;exit;*/




        if($extensionUser){

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $postCodes = [];
            $collection1= $objectManager->create('Webkul\MpHyperLocal\Model\ShipAreaFactory')->create()
            ->getCollection()
            ->addFieldToSelect('seller_id')
            ->addFieldToFilter('address_type', 'postcode')          
            ->addFieldToFilter('postcode', $extensionUser);
            $sellerId = $collection1->getColumnValues('seller_id');
            
            #print_r($sellerId);exit;
            $collectionpostcode = $objectManager->create('Webkul\MpHyperLocal\Model\ShipAreaFactory')->create()
            ->getCollection()
            ->addFieldToSelect('postcode')
            ->addFieldToFilter('address_type', 'postcode')          
            ->addFieldToFilter('seller_id', $sellerId);
            $postCodes = $collectionpostcode->getColumnValues('postcode');
            $postCodes = implode(",",$postCodes);

#rint_r($postCodes);exit;
            $collection->getSelect()
            ->join(array('payment' =>'quote_address'), $this->sales_order_table . '.entity_id= payment.quote_id',
                array('postcode' => 'payment.postcode',
                    'quote_id' => $this->sales_order_table.'.entity_id'
                )
            );
            //$collection->getSelect()->where("postcode=$extensionUser");
            $collection->getSelect()->where("address_type='shipping'");
            $collection->addFieldToFilter('postcode', ['in' => $postCodes]);
           # echo $collection->getSelect()->__toString();exit;
        }

        $this->setCollection($collection);
        parent::_prepareCollection();


        if ($this->_isExport) {
            $collection->setPageSize(null);
        }
        $this->getCollection()->resolveCustomerNames();
        return $this;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('gridAbandoned');
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'customer_name',
            [
                'header' => __('Customer'),
                'index' => 'customer_name',
                'sortable' => false,
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );

        $this->addColumn(
            'email',
            [
                'header' => __('Email'),
                'index' => 'email',
                'sortable' => false,
                'header_css_class' => 'col-email',
                'column_css_class' => 'col-email'
            ]
        );


        //add Company field
        $this->addColumn(
            'postcode',
            [
                'header' => __('Postcode'),
                'index' => 'postcode',
                'sortable' => false,
                'filter' => false,
                'renderer' => 'Lof\AdvancedReports\Block\Adminhtml\Grid\Column\Renderer\Postcode',
            ]
        );
        $this->addColumn(
            'telephone',
            [
                'header' => __('Phone Number'),
                'index' => 'telephone',
                'sortable' => false,
                'renderer' => 'Superbazaar\General\Block\Adminhtml\Grid\Column\Renderer\PhoneNumber',
                'filter' => false,
            ]
        );

        $this->addColumn(
            'items_count',
            [
                'header' => __('Products'),
                'index' => 'items_count',
                'sortable' => false,
                'type' => 'number',
                'header_css_class' => 'col-number',
                'column_css_class' => 'col-number'
            ]
        );

        $this->addColumn(
            'items_qty',
            [
                'header' => __('Quantity'),
                'index' => 'items_qty',
                'sortable' => false,
                'type' => 'number',
                'header_css_class' => 'col-qty',
                'column_css_class' => 'col-qty'
            ]
        );

        if ($this->getRequest()->getParam('website')) {
            $storeIds = $this->_storeManager->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } elseif ($this->getRequest()->getParam('group')) {
            $storeIds = $this->_storeManager->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
        } elseif ($this->getRequest()->getParam('store')) {
            $storeIds = [(int)$this->getRequest()->getParam('store')];
        } else {
            $storeIds = [];
        }
        $this->setStoreIds($storeIds);
        $currencyCode = $this->getCurrentCurrencyCode();

        $this->addColumn(
            'subtotal',
            [
                'header' => __('Subtotal'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'subtotal',
                'sortable' => false,
                'renderer' => \Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Currency::class,
                'rate' => $this->getRate($currencyCode),
                'header_css_class' => 'col-subtotal',
                'column_css_class' => 'col-subtotal'
            ]
        );

        $this->addColumn(
            'coupon_code',
            [
                'header' => __('Applied Coupon'),
                'index' => 'coupon_code',
                'sortable' => false,
                'header_css_class' => 'col-coupon',
                'column_css_class' => 'col-coupon'
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created'),
                'type' => 'datetime',
                'index' => 'created_at',
                'filter_index' => 'main_table.created_at',
                'sortable' => false,
                'header_css_class' => 'col-created',
                'column_css_class' => 'col-created'
            ]
        );

        $this->addColumn(
            'updated_at',
            [
                'header' => __('Updated'),
                'type' => 'datetime',
                'index' => 'updated_at',
                'filter_index' => 'main_table.updated_at',
                'sortable' => false,
                'header_css_class' => 'col-updated',
                'column_css_class' => 'col-updated'
            ]
        );

        $this->addColumn(
            'remote_ip',
            [
                'header' => __('IP Address'),
                'index' => 'remote_ip',
                'sortable' => false,
                'header_css_class' => 'col-ip',
                'column_css_class' => 'col-ip'
            ]
        );

        $this->addExportType('*/*/exportAbandonedCsv', __('CSV'));
        $this->addExportType('*/*/exportAbandonedExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
    * Get rows url
    *
    * @param \Magento\Framework\DataObject $row
    *
    * @return string
    */
    public function getRowUrl($row)
    {
        return $this->getUrl('customer/index/edit', ['id' => $row->getCustomerId(), 'active_tab' => 'cart']);
    }


    /**
    * Get rows url
    *
    * @param \Magento\Framework\DataObject $row
    *
    * @return string
    */

}
