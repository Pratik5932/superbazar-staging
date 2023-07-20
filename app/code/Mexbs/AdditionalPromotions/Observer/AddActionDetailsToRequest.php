<?php
namespace Mexbs\AdditionalPromotions\Observer;

use Magento\Framework\Event\ObserverInterface;

class AddActionDetailsToRequest implements ObserverInterface{
    protected $apHelper;

    public function __construct(
        \Mexbs\AdditionalPromotions\Helper\Data $apHelper
    ) {
        $this->apHelper = $apHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();

        $data = $request->getPostValue();

        if(isset($data['rule']['action_details'])){
            $data['action_details'] = $data['rule']['action_details'];
        }

        if(isset($data['simple_action'])
            && $this->apHelper->isSimpleActionAp($data['simple_action'])){
            $data['rule']['actions'] = [];
            $data['actions'] = [];
            $data['actions_serialized'] = "";
        }

        $request->setPostValue($data);
    }
}