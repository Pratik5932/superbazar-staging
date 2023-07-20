<?php
namespace Mexbs\AdditionalPromotions\Controller\Action;

use Magento\Catalog\Api\ProductRepositoryInterface;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;

class AddPromoProductsToCart extends \Magento\Framework\App\Action\Action
{
    private $resultJsonFactory;
    private $cart;
    private $helper;
    private $ruleFactory;
    private $productRepository;
    private $storeManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mexbs\AdditionalPromotions\Helper\Data $helper
    ){
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cart = $cart;
        $this->helper = $helper;
        $this->ruleFactory = $ruleFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function _initProduct($productId)
    {
        $storeId = $this->storeManager->getStore()->getId();
        try {
            return $this->productRepository->getById($productId, false, $storeId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }


    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $productsAddData = json_decode($params['products_add_data'], true);

        foreach($productsAddData as $productAddData){
            $product = $this->_initProduct($productAddData['product_id']);

            $productAddToCartParams = [
                'product_id' => $productAddData['product_id'],
                'qty' => (isset($productAddData['qty']) ? $productAddData['qty'] : 1),
                'super_attribute' => []
            ];

            if(isset($productAddData['options'])){
                foreach($productAddData['options'] as $productAddDataOptionKey => $productAddDataOptionVal){
                    if(isset($productAddDataOptionVal['attribute_id'])){
                        $productAddToCartParams['super_attribute'][$productAddDataOptionVal['attribute_id']] = $productAddDataOptionVal['option_id'];
                    }else{
                        if(!isset($productAddToCartParams['options'])){
                            $productAddToCartParams['options'] = [];
                        }
                        $productAddToCartParams['options'][$productAddDataOptionKey] = $productAddDataOptionVal;
                    }
                }
            }

            $this->cart->addProduct($product, $productAddToCartParams);

            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );
        }
        $this->cart->save();

        $this->_view->loadLayout();
        return $this->resultJsonFactory->create()->setData([
            'status' => 'success',
            'cart_html' => $this->_view->getLayout()->getBlock('checkout.cart')->toHtml()
        ]);
    }
}