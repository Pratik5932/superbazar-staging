<?php

namespace MageArray\StorePickup\Block;

class Storepickup extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \MageArray\StorePickup\Model\StoreFactory $storeFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->storeFactory = $storeFactory;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    public function getStoreCollection()
    {
        $coll = $this->storeFactory->create()->getCollection();
        $coll->setOrder('sort_order', 'ASC');

        return $this->jsonHelper->jsonEncode($coll->getData());
    }

}