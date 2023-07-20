<?php

namespace Cminds\AdvancedPermissions\Block\Adminhtml\Role\Grid;

use Cminds\AdvancedPermissions\Model\RoleProductFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Registry;

/**
 * Class Product
 *
 * @package Cminds\AdvancedPermissions\Block\Adminhtml\Role\Grid
 */
class Product extends Extended
{
    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var RoleProductFactory
     */
    private $roleProductFactory;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * Product constructor.
     *
     * @param Context                                  $context
     * @param Data                                     $backendHelper
     * @param ProductFactory                           $productFactory
     * @param Registry                                 $coreRegistry
     * @param RoleProductFactory                       $roleProductFactory
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Magento\Customer\Model\Session          $customerSession
     * @param array                                    $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        ProductFactory $productFactory,
        Registry $coreRegistry,
        RoleProductFactory $roleProductFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->productFactory = $productFactory;
        $this->roleProductFactory = $roleProductFactory;
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
        $this->customerSession = $customerSession;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('role/product_grid_js.phtml');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('asc');
        $this->setId('roleProductGrid');
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection with products.
     *
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->productFactory
            ->create()
            ->getCollection()
            ->addAttributeToSelect("*");

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns.
     *
     * @return $this
     *
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_role_product',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_role_product', // TODO change name in observer
                'values' => $this->getSelectedProducts(),
                'align' => 'center',
                'field_name' => 'in_role_product[]',
                'index' => 'entity_id'
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('Product Id'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Product Name'),
                'index' => 'name'
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('Sku'),
                'index' => 'sku'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Get grid url, it will be used for AJAX calls like a pagination, filters.
     *
     * @return string
     */
    public function getGridUrl()
    {
        $roleId = $this->getRequest()->getParam('rid');
        $gridUrl = $this->getUrl('advancedpermissions/role/productsgrid', ['rid' => $roleId]);

        return $gridUrl;
    }

    /**
     * Set previously selected products to the session.
     *
     * @param null $newTempValues
     */
    public function setSelectedProducts($newTempValues = null)
    {
        if ($newTempValues !== null) {
            $decodedNewTempValues = $this->jsonDecoder->decode($newTempValues);
            $oldTempValues = $this->customerSession->getSelectedProductsTempValues();

            if ($oldTempValues === null) {
                $oldTempValues = [];
            }

            foreach ($decodedNewTempValues as $index => $value) {
                $oldTempValues[$index] = $value;
            }

            $this->customerSession->setSelectedProductsTempValues($oldTempValues);
        }
    }

    /**
     * Get selected products.
     *
     * @return array|string
     */
    public function getSelectedProducts()
    {
        $roleId = $this->getRequest()->getParam(
            'rid'
        ) > 0 ? $this->getRequest()->getParam(
            'rid'
        ) : $this->coreRegistry->registry(
            'RID'
        );

        $roleProduct = $this->roleProductFactory->create()->load($roleId);

        if ($roleProduct->getRoleId() === null || $roleProduct->getRoleId() === '') {
             return [];
        }

        $selectedProducts = explode(',', $roleProduct->getReferenceValue());
        $tempSelectedValues = $this->customerSession->getSelectedProductsTempValues();

        if ($tempSelectedValues !== null) {
            foreach ($tempSelectedValues as $index => $value) {
                if ($value == 1) {
                    $selectedProducts[] = $index;
                } elseif ($value == 0) {
                    $keyToDrop = array_search($index, $selectedProducts);
                    unset($selectedProducts[$keyToDrop]);
                }
            }
        }

        return $selectedProducts;
    }
}
