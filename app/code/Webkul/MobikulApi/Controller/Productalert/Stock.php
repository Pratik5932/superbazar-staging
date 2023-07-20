<?php
/**
* Webkul Software.
*
*
*
* @category  Webkul
* @package   Webkul_MobikulApi
* @author    Webkul <support@webkul.com>
* @copyright Webkul Software Private Limited (https://webkul.com)
* @license   https://store.webkul.com/license.html ASL Licence
* @link      https://store.webkul.com/license.html
*/

namespace Webkul\MobikulApi\Controller\Productalert;

class Stock extends \Webkul\MobikulApi\Controller\ApiController
{
    protected $emulate;
    protected $jsonHelper;
    protected $storeManager;
    protected $productLoader;
    protected $productAlertStock;

    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Store\Model\App\Emulation $emulate,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\MobikulCore\Helper\Catalog $helperCatalog,
        \Magento\ProductAlert\Model\Stock $productAlertStock,
        \Magento\Catalog\Model\ProductFactory $productLoader,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->emulate = $emulate;
        $this->jsonHelper = $jsonHelper;
        $this->storeManager = $storeManager;
        $this->productLoader = $productLoader;
        $this->productAlertStock = $productAlertStock;
        parent::__construct($helper, $context, $jsonHelper);
    }

    public function execute()
    {
        try {
            if ($this->wholeData) {
                $storeId = $this->wholeData["storeId"] ?? 1;
                $productId = $this->wholeData["productId"] ?? 0;
                $customerToken = $this->wholeData["customerToken"] ?? "";
                $customerId = $this->helper->getCustomerByToken($customerToken) ?? 0;
                // Checking customer token //////////////////////////////////////
                if (!$customerId && $customerToken != "") {
                    $returnArray["message"] = __(
                        "As customer you are requesting does not exist, so you need to logout."
                    );
                    $returnArray["otherError"] = "customerNotExist";
                    $customerId = 0;
                }
                // End checking customer token //////////////////////////////////
                $environment = $this->emulate->startEnvironmentEmulation($storeId);
                $product = $this->productLoader->create()->load($productId);
                $model = $this->productAlertStock
                ->setCustomerId($customerId)
                ->setProductId($product->getId())
                ->setPrice($product->getFinalPrice())
                ->setWebsiteId($this->storeManager->getStore()->getWebsiteId());
                $model->save();

                $this->inlineTranslation = $this->_objectManager->get('\Magento\Framework\Translate\Inline\StateInterface');
                $this->transportBuilder = $this->_objectManager->get('\Magento\Framework\Mail\Template\TransportBuilder');
                $this->scopeConfig = $this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');

                $senderEmail = $this->scopeConfig->getValue('trans_email/ident_custom1/email');
                $senderName = $this->scopeConfig->getValue('trans_email/ident_custom1/name');
                $emailTo = $senderEmail;

                # $recipients = explode(",",$emailTo);
                $subject= "Product Out of stock alert";

                $emailTemplateVariables = array(); 

                $customerData = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerId);


                $table = "";
                $table .= "<p>Out of Stock alert subscribed!!</p>";

                $table .= '<table width="800px">
                <thead>
                <th align="left" style="padding: 10px 0;font-size: 14px;border: 1px solid;padding: 5px;">Customer Name</th>
                <th align="left" style="padding: 10px 0;font-size: 14px;border: 1px solid;padding: 5px;">Email</th>
                <th align="left" style="padding: 10px 0;font-size: 14px;border: 1px solid;padding: 5px;">Product Name</th>
                <th align="left" style="padding: 10px 0;font-size: 14px;border: 1px solid;padding: 5px;">Product Sku</th>
                </thead>
                ';
                $table .="
                <tr> <td align='left' style='border: 1px solid;width: 200px;padding: 5px;'>".$customerData->getName()."</td>
                <td align='left' style='border: 1px solid;width: 400px;padding: 5px;'>".$customerData->getEmail()."</td>
                <td align='left' style='border: 1px solid;padding: 5px;width: 100px'>".$product->getName()."</td>
                <td align='left' style='border: 1px solid;width: 300px;padding: 5px;'>".$product->getSku()." </td>
                </tr>";
                $table .=' </table>';


                $emailTemplateVariables = [
                    'data' => $table,
                    'subject'    => $subject
                ];

                $this->inlineTranslation->suspend();

                $postObject = new \Magento\Framework\DataObject();
                $transport = $this->transportBuilder
                ->setTemplateIdentifier('stock_notofication')
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars($emailTemplateVariables)
                ->setFrom(['name' =>$senderName,'email' => $senderEmail])
                ->addTo($senderEmail,$senderName)
                ->getTransport();

                $transport->sendMessage();

                $sellerproduct = $this->_objectManager->create('Webkul\Marketplace\Model\Product')->load($product->getId(),'mageproduct_id');
                if($sellerproduct->getId()){
                    $seller = $this->_objectManager->get('Magento\Customer\Model\Customer')->load($sellerproduct->getData('seller_id'));
                    $sellerEmail = $seller->getEmail(); 
                    $sellerName = $seller->getSellerName(); 
                    $transport1 = $this->transportBuilder
                    ->setTemplateIdentifier('product_expire_send')
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                        ]
                    )
                    ->setTemplateVars($emailTemplateVariables)
                    ->setFrom(['name' =>$senderName,'email' => $senderEmail])
                    ->addTo($sellerEmail,$sellerName)
                    ->getTransport();

                    $transport1->sendMessage();
                }

                $this->inlineTranslation->resume();

                $returnArray["message"] = __("Alert subscription has been saved.");
                $returnArray["success"] = true;
                $this->emulate->stopEnvironmentEmulation($environment);
                return $this->getJsonResponse($returnArray);
            } else {
                throw new \BadMethodCallException(__("Invalid Request"));
            }
        } catch (\NoSuchEntityException $noEntityException) {
            $returnArray["message"] = __("There are not enough parameters.");
            $this->helper->printLog($returnArray);
            return $this->getJsonResponse($returnArray);
        } catch (\Exception $e) {
            $returnArray["message"] = __("We can't update the alert subscription right now.");
            $this->helper->printLog($e->getMessage());
            return $this->getJsonResponse($returnArray);
        }
    }
}
