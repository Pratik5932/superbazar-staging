<?php
namespace Mexbs\AdditionalPromotions\Controller\Action;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;

class GetPromoProductsHtml extends \Magento\Framework\App\Action\Action
{
    private $resultJsonFactory;
    private $cart;
    private $helper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Mexbs\AdditionalPromotions\Helper\Data $helper
    ){
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cart = $cart;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $quote = $this->cart->getQuote();
        $rulesHtml = $this->helper->getPromoBlockRulesHtmlArray($quote);
        return $this->resultJsonFactory->create()->setData($rulesHtml);
    }
}