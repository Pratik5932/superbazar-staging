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
namespace Ced\Wallet\Model;

use Ced\Wallet\Model\ResourceModel\Transaction\Collection;

class TransactionDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry;
    /**
     * @var Collection
     */
    protected $collection;
    /**
     * @var array
     */
    protected $addFieldStrategies;

    /**
     * @var array
     */
    protected $addFilterStrategies;

    /**
     * FormDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Collection $collectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory;
        $this->_coreRegistry = $registry;
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
        $this->objectManager = $objectManager;
    }

    /**
     * Get data
     *
     * @return array
     */
/*    public function getData()
    {
        $data = $this->_coreRegistry->registry('customer_wallet');

        if($data && $id = $data->getId()) {
            $transactionData = $data->toArray();
            $arr = [$id => ['transaction_form' => []]];
            foreach ($transactionData as $key => $value) {
                $arr[$id]['transaction_form'][$key] = $value;
            }
            $arr[$id]['transaction_form']['id'] = $id;
            $arr[$id]['transaction_form']['customer_id'] = $id;
            print_r($arr);

        }
        else{
            $arr = [];
        }
        return $arr;
    }*/

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $groupData = $this->_coreRegistry->registry('customer_wallet');


/*
        if(is_object($groupData) && $groupData->getId()){
            $this->loadedData[$groupData->getId()]['transaction_form']['id'] = $groupData->getId();
             $this->loadedData[$groupData->getId()]['transaction_form']['customer_id'] = $groupData->getId();
        }*/

        $this->loadedData[$groupData->getId()]['customer_id'] = $groupData->getId();
        return $this->loadedData;
    }
}