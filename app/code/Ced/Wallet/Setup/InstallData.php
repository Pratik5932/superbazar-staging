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

namespace Ced\Wallet\Setup;

use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\DB\Ddl\Table;
use PhpCsFixer\Tokenizer\Transformer\TypeAlternationTransformer;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{

    public $_objectManager;
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;
    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    public function __construct(CustomerSetupFactory $customerSetupFactory,
                                AttributeSetFactory $attributeSetFactory,
                                \Magento\Framework\ObjectManagerInterface $objectManager,
                                \Magento\Framework\App\State $state
    )
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->_objectManager = $objectManager;
        $state->setAreaCode('frontend');
    }


    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'enable_wallet_system',
            [
                'type' => 'int',
                'label' => 'Enable Wallet System',
                'required' => 0,
                'default' => 1,
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'system' => 0, // <-- important, otherwise values aren't saved.
                // @see Magento\Customer\Model\Metadata\CustomerMetadata::getCustomAttributesMetadata()
                'position' => 100
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'enable_wallet_system')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer'],
            ]);

        $attribute->save();

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'amount_wallet',
            [
                'type' => 'decimal',
                'label' => 'Amount in Wallet',
                'required' => 0,
                'input' => 'text',
                'default' => 0,
                'system' => 0, // <-- important, otherwise values aren't saved.
                // @see Magento\Customer\Model\Metadata\CustomerMetadata::getCustomAttributesMetadata()
                'frontend_class' => 'not-negative-amount',
                'position' => 110
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'amount_wallet')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer'],
            ]);

        $attribute->save();


        $_product = $this->_objectManager->create('Magento\Catalog\Model\Product');
        $_product->setName('Wallet Pay');
        $_product->setTypeId('virtual');
        $_product->setAttributeSetId(4);
        $_product->setSku('wallet_product');
        $_product->setWebsiteIds(array(1));
        $_product->setVisibility(1);
        $_product->setPrice(100);
        $_product->setStatus(1);
        $_product->setTaxClassId(0);

        $_product->setStockData(array(
                'use_config_manage_stock' => 0, //'Use config settings' checkbox
                'manage_stock' => 1, //manage stock
                'min_sale_qty' => 1, //Minimum Qty Allowed in Shopping Cart
                'max_sale_qty' => 1, //Maximum Qty Allowed in Shopping Cart
                'is_in_stock' => 1, //Stock Availability
                'qty' => 100 //qty
            )
        );

        $_product->save();

        $customers = $this->_objectManager->create('Magento\Customer\Model\Customer')->getCollection();
        foreach ($customers as $customer) {
            $customer1 = $this->_objectManager->create('Magento\Customer\Model\CustomerFactory')->create();
            $customerData = $customer1->getDataModel();
            $customerData->setId($customer->getEntityId());
            $customerData->setCustomAttribute('enable_wallet_system', 1);
            $customer1->updateData($customerData);
            $customerResource = $this->_objectManager->create('Magento\Customer\Model\ResourceModel\CustomerFactory')->create();

            if ($customer->getEntityId() != "") {
                $customerResource->saveAttribute($customer1, 'enable_wallet_system');
            }
        }
    }
}