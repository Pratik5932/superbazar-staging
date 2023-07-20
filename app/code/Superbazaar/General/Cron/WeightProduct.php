<?php
/**
*
* Copyright © 2015 Spaargcommerce. All rights reserved.
*/
namespace Superbazaar\General\Cron;
use Magento\Framework\Mail\Template\TransportBuilder;

class WeightProduct
{
    protected $collectionFactory;
    protected $date;
    protected $inlineTranslation;
    protected $_messageManager;
    protected $scopeConfig;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,

        TransportBuilder $transportBuilder

    ) {
        $this->collectionFactory = $collectionFactory;
        $this->date = $date;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;

    }    
    public function execute()
    {
        $productCollection = $this->collectionFactory->create();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $Ids=array(11466,11473);

        $productCollection->addAttributeToSelect('*')
        ->addFieldToFilter('entity_id', ['in' => $Ids]);
    
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $senderEmail = "admin@superbazaar.com.au";
        $senderName = "Admin";
        $emailTo = "info@superbazaar.com.au";
        $subject= "Report Of Panner Products";

        $emailTemplateVariables = array(); 
        $table = "";
        $table .= '<table width="800px">
        <thead>
        <th align="left" style="padding: 10px 0;font-size: 14px;border: 1px solid;padding: 5px;">Product Sku</th>
        <th align="left" style="padding: 10px 0;font-size: 14px;border: 1px solid;padding: 5px;">Weights</th>
        </thead>
        ';
        foreach($productCollection as $product){

            $table .="
            <tr> <td align='left' style='border: 1px solid;width: 200px;padding: 5px;'>".$product->getSku()."</td>
            <td align='left' style='border: 1px solid;width: 400px;padding: 5px;'>".$product->getWeights()."</td>
            </tr>";
        }
        $table .=' </table>';

        $emailTemplateVariables = [
            'data' => $table,
            'subject'    => $subject
        ];
        $this->inlineTranslation->suspend();
        try {

            $postObject = new \Magento\Framework\DataObject();

            $transport = $this->transportBuilder
            ->setTemplateIdentifier('product_expire_send')
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom(['name' =>$senderName,'email' => $senderEmail])
            ->addTo($emailTo)
            ->getTransport();

            $transport->sendMessage();

            $this->inlineTranslation->resume();
            return $this;

        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addError(
                __('We can\'t process your request right now. Sorry, that\'s all we know.'.$e->getMessage())
            );
            return $this;

        }
    }
}