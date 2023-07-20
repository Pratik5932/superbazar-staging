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
namespace Ced\Wallet\Model\Config\Source;

class Customer extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * Default values for option cache
     *
     * @var array
     */
    protected $_optionsDefault = [];

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     */
    protected $_attrOptionCollectionFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory
     */
    protected $_attrOptionFactory;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory,
    	\Magento\Framework\ObjectManagerInterface $objectManager
    		
    ) {
        $this->_attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->_attrOptionFactory = $attrOptionFactory;
        $this->_objectManager = $objectManager;
    }

    /**
     * Retrieve Full Option values array
     *
     * @param bool $withEmpty       Add empty option to array
     * @param bool $defaultValues
     * @return array
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (!is_array($this->_optionsDefault)) {
            $this->_optionsDefault = [];
        }
    	
    	$options =[];
        $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->getCollection();
        
        $i=0; 
      
        foreach ($customer as $values)
        {
        	$options[$i]['value'] = $values->getId();
        	$options[$i]['label'] = $values->getName();
        	$i++;
         }
         
        if ($withEmpty) {
            $options = $this->addEmptyOption($options);
        }
        return $options;
    }
    
    /**
     * Add an empty option to the array
     *
     * @param array $options
     * @return array
     */
    private function addEmptyOption(array $options)
    {
        array_unshift($options, ['label' => ' ', 'value' => '']);
        return $options;
    }

   
}
