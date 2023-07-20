<?php

namespace Ced\Wallet\Block\Sales\Order;



class Wallet extends \Magento\Sales\Block\Order\Totals
{
    /**
     * Tax configuration model
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $_config;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_config = $taxConfig;
        parent::__construct($context,$registry, $data);

    }

    /**
     * Check if we nedd display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->_source;
    }
    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Initialize all order totals relates with tax
     *
     * @return \Magento\Tax\Block\Sales\Order\Tax
     */
    public function initTotals()
    {

        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
       // var_dump($this->_source->getData()); die("qq");

        $store = $this->getStore();

        $walletPayment = new \Magento\Framework\DataObject(
            [
                'code' => 'wallet_payment',
                'strong' => false,
                //'value' => 100,
                'value' => $this->_order->getWalletPayment(),
                'label' => __('Wallet Payment'),
            ]
        );

        if($this->_order->getWalletPayment()){
            $parent->addTotal($walletPayment, 'wallet_payment');
        }
        // $this->_addTax('grand_total');


        return $this;
    }

}