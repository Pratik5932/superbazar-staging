<?php
namespace Superbazaar\General\Block\Total;

class Saving extends \Magento\Framework\View\Element\Template
{
    /**
    * Tax configuration model
    *
    * @var \Magento\Tax\Model\Config
    */
    protected $config;

    /**
    * @var Order
    */
    protected $order;

    /**
    * @var \Magento\Framework\DataObject
    */
    protected $source;

    /**
    * @param \Magento\Framework\View\Element\Template\Context $context
    * @param \Magento\Tax\Model\Config $taxConfig
    * @param array $data
    * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
    */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        array $data = []
    ) {
        $this->config = $taxConfig;
        parent::__construct($context, $data);
    }


    /**
    * Get data (totals) source model
    *
    * @return \Magento\Framework\DataObject
    */
    public function getSource()
    {
        return $this->source;
    }

    /**
    * @return Order
    */
    public function getOrder()
    {
        return $this->order;
    }

    /**
    * Initialize all order totals relates with tax
    *
    * @return \Magento\Tax\Block\Sales\Order\Tax
    */
    public function initTotals()
    {

        $parent = $this->getParentBlock();
        $this->order = $parent->getOrder();

        $this->source = $parent->getSource();
        $store = $this->getStore();
        $order = $this->order->load($this->order->getId());
        $orderItems = $order->getAllItems();
        $totalDiscountprice =0;
        foreach ($order->getAllItems() as $_item) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $product = $objectManager->create('Magento\Catalog\Model\Product')->load($_item->getProductId());
            $specialprice = $product->getSpecialPrice();
            $specialPriceFromDate = $product->getSpecialFromDate();
            $specialPriceToDate = $product->getSpecialToDate();
            $today =  time();
            if($specialprice && ($product->getPrice()>$_item->getPrice())){
                $mainprice = $_item->getPrice();
                $discountPrice = $product->getPrice()-$mainprice; 
                $discountPriceCal=  $discountPrice*$_item->getQtyOrdered();
                $totalDiscountprice+= $discountPriceCal+$_item->getDiscountAmount();
                //$discountPriceCalwithotherdiscount = $totalDiscountprice+$_item->getDiscountAmount();

                //$totalDiscountprice= $this->helper('Magento\Framework\Pricing\Helper\Data')->currency($discountPriceCal,true,false);
            }else{
                $totalDiscountprice+= $_item->getDiscountAmount();
            }
            #echo $totalDiscountprice;exit;
        }
        $totalSaving = $objectManager->get('Magento\Framework\Pricing\Helper\Data')->currency($totalDiscountprice,true,false);


        if ($totalSaving) {
            $charges = new \Magento\Framework\DataObject(
                [
                    'code' => 'processing_fee',
                    'strong' => false,
                    'value' => $totalSaving,
                    'label' => __('Total Saving'),
                ]
            );
            $parent->addTotal($charges, 'processing_fee');
            $parent->addTotal($charges, 'processing_fee');
        }
        return $this;
    }
}