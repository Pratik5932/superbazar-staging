<?php
namespace Mexbs\AdditionalPromotions\Controller\Action;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;

class GetPromoAddToCartHtml extends \Magento\Framework\App\Action\Action
{
    private $resultJsonFactory;
    private $cart;
    private $helper;
    private $ruleFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Mexbs\AdditionalPromotions\Helper\Data $helper
    ){
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cart = $cart;
        $this->helper = $helper;
        $this->ruleFactory = $ruleFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $quote = $this->cart->getQuote();

        $ruleId = $this->getRequest()->getParam('rule_id');
        $rule = $this->ruleFactory->create()->load($ruleId);

        $ruleActionDetailModel = $this->helper->getLoadedActionDetail($rule);
        /**
         * @var \Mexbs\AdditionalPromotions\Model\Rule\Action\Details\Condition\Product\Combine $ruleActionDetailModel
         */
        $cartOnAddModalContentBlockType = $ruleActionDetailModel->getCartOnAddModalContentBlockType();
        /**
         * @var \Magento\Framework\View\Element\Template $cartOnAddModalContentBlock
         */

        $cartOnAddModalContentBlock = $this->_objectManager->create($cartOnAddModalContentBlockType,
            [
                'rule' => $rule,
                'quote' => $quote
            ]
        );

        $this->_view->loadLayout();

        return $this->resultJsonFactory->create()->setData([
            'added' => 'false',
            'html' => $cartOnAddModalContentBlock->toHtml()
        ]);
    }
}