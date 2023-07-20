<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Email;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\Template\TransportBuilderByStore;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;

/**
 * Sender Builder
 */
class SenderBuilder
{
    /**
     * @var Template
     */
    protected $templateContainer;

    /**
     * @var IdentityInterface
     */
    protected $identityContainer;

    /**
     * @var TransportBuilder
     */
	     private $checkoutSession;

    protected $transportBuilder;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Template $templateContainer
     * @param IdentityInterface $identityContainer
     * @param TransportBuilder $transportBuilder
     * @param TransportBuilderByStore $transportBuilderByStore
     */
    public function __construct(
        Template $templateContainer,
        IdentityInterface $identityContainer,
        TransportBuilder $transportBuilder,
		        \Magento\Checkout\Model\Session $checkoutSession,

        TransportBuilderByStore $transportBuilderByStore = null
    ) {
        $this->templateContainer = $templateContainer;
        $this->identityContainer = $identityContainer;
		$this->checkoutSession = $checkoutSession;

        $this->transportBuilder = $transportBuilder;
    }

    /**
     * Prepare and send email message
     *
     * @return void
     */
    public function send()
    {
		
		
        $this->configureEmailTemplate();

        $this->transportBuilder->addTo(
            $this->identityContainer->getCustomerEmail(),
            $this->identityContainer->getCustomerName()
        );


		$vars = $this->templateContainer->getTemplateVars();
		$order = $vars['order'];
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
         $mpProCollection = $objectManager->create('Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory');
            $mpHelper = $objectManager->get('Webkul\Marketplace\Helper\Data');
            
            foreach ($order->getAllItems() as $item) {
                $productId = $item->getProductId();
                $sellerId = $mpProCollection->create()
                    ->addFieldToFilter('mageproduct_id', $productId)
                    ->setPageSize(1)
                    ->getFirstItem()
                    ->getSellerId();                       
            }
            $SellerData = $objectManager->create('Magento\Customer\Model\Customer')->load($sellerId);
            $sellerEmail = $SellerData->getEmail();
            
		#$lastOrderId = $objectManager->get('Magento\Checkout\Model\Session')->getData('last_order_id');
		#$lastOrderId = $this->checkoutSession->getData('last_order_id');


/*foreach ($items as $item) {
        $itemid = $item->getProductId();
}*/


		    // $lastOrderId = $order['entity_id'];
			// mail("pratik.conceptivecommerce@gmail.com","testasdad",$lastOrderId);

		$lastOrderId = "42281";
		#echo $lastOrderId;exit;
		 $sellerOrder = $objectManager->create('Webkul\Marketplace\Model\OrdersFactory')->create()
        ->getCollection()
        ->addFieldToFilter('order_id', $lastOrderId)
        ->addFieldToFilter('seller_id', ['neq' => 0]);
		#echo count($sellerOrder);exit;
		//$useremail = "";
		
		#mail("er.bharatmali@gmail.com","count",$query);
		foreach ($sellerOrder as $info) {
			$userdata = $objectManager->get("Magento\Customer\Api\CustomerRepositoryInterface")->getById($info['seller_id']);
            // $useremail = $userdata->getEmail();
            $useremail = $sellerEmail;
            
			#mail("pratik.conceptivecommerce@gmail.com","test",$useremail);
			#mail("er.bharatmali@gmail.com","testasdad",$lastOrderId);
			$this->transportBuilder->addBcc($useremail);
		}
		#echo $useremail."------id".$lastOrderId;exit;
		#mail("pratik.conceptivecommerce@gmail.com","test",$useremail);
		
        $copyTo = $this->identityContainer->getEmailCopyTo();

        if (!empty($copyTo) && $this->identityContainer->getCopyMethod() == 'bcc') {
            foreach ($copyTo as $email) {
                $this->transportBuilder->addBcc($email);
            }
        }
		
		
		
        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }

    /**
     * Prepare and send copy email message
     *
     * @return void
     */
    public function sendCopyTo()
    {
        $copyTo = $this->identityContainer->getEmailCopyTo();

        if (!empty($copyTo)) {
            foreach ($copyTo as $email) {
                $this->configureEmailTemplate();
                $this->transportBuilder->addTo($email);
                $transport = $this->transportBuilder->getTransport();
                $transport->sendMessage();
            }
        }
    }

    /**
     * Configure email template
     *
     * @return void
     */
    protected function configureEmailTemplate()
    {
        $this->transportBuilder->setTemplateIdentifier($this->templateContainer->getTemplateId());
        $this->transportBuilder->setTemplateOptions($this->templateContainer->getTemplateOptions());
        $this->transportBuilder->setTemplateVars($this->templateContainer->getTemplateVars());
        $this->transportBuilder->setFromByScope(
            $this->identityContainer->getEmailIdentity(),
            $this->identityContainer->getStore()->getId()
        );
    }
}
