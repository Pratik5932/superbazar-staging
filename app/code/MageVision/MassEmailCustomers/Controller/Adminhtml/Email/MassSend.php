<?php
/**
* MageVision Mass Email Customers Extension
*
* @category     MageVision
* @package      MageVision_MassEmailCustomers
* @author       MageVision Team
* @copyright    Copyright (c) 2021 MageVision (http://www.magevision.com)
* @license      http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
declare(strict_types=1);

namespace MageVision\MassEmailCustomers\Controller\Adminhtml\Email;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as SalesCollectionFactory;
use MageVision\MassEmailCustomers\Model\Config;
use Magento\Framework\App\Area;
use Magento\Sales\Model\Order;
use Magento\Framework\Exception\MailException;
use Magento\Backend\Model\View\Result\Redirect;

class MassSend extends Action
{
    /**
    * @var Filter
    */
    protected $filter;

    /**
    * @var salesCollectionFactory
    */
    protected $salesCollectionFactory;

    /**
    * @var CustomerCollectionFactory
    */
    protected $customerCollectionFactory;

    /**
    * @var Config
    */
    protected $config;

    /**
    * @var TransportBuilder
    */
    protected $transportBuilder;

    /**
    * @var StateInterface
    */
    protected $inlineTranslation;

    /**
    * @param Context $context
    * @param Filter $filter
    * @param Config $config
    * @param CustomerCollectionFactory $customerCollectionFactory
    * @param SalesCollectionFactory $salesCollectionFactory
    * @param TransportBuilder $transportBuilder
    * @param StateInterface $inlineTranslation
    */
    public function __construct(
        Context $context,
        Filter $filter,
        Config $config,
        CustomerCollectionFactory $customerCollectionFactory,
        SalesCollectionFactory $salesCollectionFactory,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation
    ) {
        $this->filter = $filter;
        $this->config = $config;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->salesCollectionFactory = $salesCollectionFactory;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        parent::__construct($context);
    }

    /**
    * Execute action
    *
    * @return Redirect
    * @throws LocalizedException
    */
    public function execute()
    {
        $namespace = $this->getRequest()->getParam('namespace');

        if ($namespace == 'customer_listing') {
            $collection = $this->filter->getCollection($this->customerCollectionFactory->create());
        } else {
            $collection = $this->filter->getCollection($this->salesCollectionFactory->create());
        }


        $emailSent = 0;
        /* seller wise email send start 
        /*     $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collectionpostcode = $objectManager->get('Webkul\MpHyperLocal\Model\ShipAreaFactory')->create()
        ->getCollection()
        ->addFieldToSelect('postcode')
        ->addFieldToFilter('address_type', 'postcode')          
        ->addFieldToFilter('seller_id', "6637");
        $postCodes = ($collectionpostcode->getColumnValues('postcode'));
        $postCodes1= array_map('trim', $postCodes);
        #print_r($postCodes1);
        $addressCollection = $objectManager->get("Magento\Customer\Model\ResourceModel\Address\Collection")->addFieldToFilter('postcode', ['in' => $postCodes1]);

        #echo $addressCollection->getSelect()->__toString();exit;
        if ($addressCollection->getSize()) {
        $parentIds = array_unique($addressCollection->getColumnValues('parent_id'));
        #print_r($parentIds);exit;
        $collection = $objectManager->get("\Magento\Customer\Model\ResourceModel\Customer\CollectionFactory")->create()->addFieldToFilter('entity_id', ['in' => $parentIds]);
        #$subject->addFieldToFilter('entity_id', ['in' => $parentIds]);
        }
        # echo $collection->getSelect()->__toString();exit;
        // seller wise email send start */
        foreach ($collection as $item) {
            #echo $item->getEmail();exit;

            $restictedaArray =array(
                "hk.masks@gmail.com",
                "vijay.pulla@gmail.com",
                "g.swethareddy498@gmail.com",
                "meher16@gmail.com",
                "abamra@hotmail.com",
                "bindu.kodali184@gmail.com",
                "simply.hir@gmail.com",
                "cso.abhi@gmail.com",
                "sss.shastry@gmail.com",
                "swapnavamshi.p@gmail.com",
                "innocentrascal@gmail.com",
                "manoj.dealmax@gmail.com",
                "vsyrkine@gmail.com");
            if(in_array($item->getEmail(),$restictedaArray)){
                continue;
            }

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $newsletterCollectionFactory = $objectManager->get('\Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory');
            $collection = $newsletterCollectionFactory->create();
            $collection->addFieldToFilter('customer_id', $item->getData('entity_id'));
            $data = $collection->getFirstItem();


            try {
                // if($data->getSubscriberStatus() == 1){
                $this->send($item);
                $emailSent++;
                // }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                break;
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Some emails were not sent.'));
                break;
            }
        }

        if ($emailSent) {
            $this->messageManager->addSuccessMessage(__('A total of %1 email(s) have been sent.', $emailSent));
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
    * @param object $item
    * @return $this
    * @throws LocalizedException
    * @throws MailException
    */
    public function send($item)
    {
        $this->inlineTranslation->suspend();

        if ($item instanceof Order) {
            $email = $item->getCustomerEmail();
            #echo $email;exit;
            $orderId = $item->getIncrementId();
            $name = $item->getCustomerFirstname().' '.$item->getCustomerLastname();
        } else {
            $email = $item->getEmail();
            $name = $item->getName();
            $orderId = '';
        }
        #exit;
        $storeId = $item->getData('store_id');
        $this->transportBuilder->setTemplateIdentifier(
            $this->config->getEmailTemplate($storeId)
        )->setTemplateOptions(
            [
                'area' => Area::AREA_FRONTEND,
                'store' => $storeId,
            ]
        )->setFromByScope(
            $this->config->getSender($storeId),
            $storeId
        )->setTemplateVars(
            [
                'customer_name' => $name,
                'customer_email' => $email,
                'increment_id' => $orderId
            ]
        )->addTo(
            $email,
            $name
        );
        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();

        $this->inlineTranslation->resume();

        return $this;
    }
}
