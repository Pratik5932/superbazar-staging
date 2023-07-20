<?php
namespace Mexbs\AdditionalPromotions\Controller\Action;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;

class GetCartHints extends \Magento\Framework\App\Action\Action
{
    private $resultJsonFactory;
    private $cart;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Cart $cart
    ){
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cart = $cart;
        parent::__construct($context);
    }

    public function execute()
    {
        return $this->resultJsonFactory->create()->setData($this->cart->getQuote()->getHintMessages());
    }
}