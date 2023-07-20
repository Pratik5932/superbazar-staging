<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Controller\Seller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

class Remove extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @param Context $context
     * @param FormKeyValidator $formKeyValidator
     * @param \Webkul\Marketplace\Helper\Data $mphelper
     * @param \Webkul\MpStripe\Model\StripeSellerFactory $stripeSellerFactory
     */
    public function __construct(
        Context $context,
        FormKeyValidator $formKeyValidator,
        \Webkul\Marketplace\Helper\Data $mphelper,
        \Webkul\MpStripe\Model\StripeSellerFactory $stripeSellerFactory
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->mphelper = $mphelper;
        $this->stripeSellerFactory = $stripeSellerFactory;
        parent::__construct($context);
    }

    /**
     * Connect to stripe.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            if (!$this->formKeyValidator->validate($this->getRequest())) {
                return $this->resultRedirectFactory->create()->setPath(
                    'mpstripe/seller/connect/',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
            $customerId = $this->mphelper->getCustomerId();
            $isPartner = $this->mphelper->isSeller();
            if ($isPartner == 1) {
                $stripeSellerColl = $this->stripeSellerFactory->create()->getCollection()
                                          ->addFieldToFilter("seller_id", ["eq"=>$customerId]);

                foreach ($stripeSellerColl as $stripe) {
                    $this->deleteObj($stripe);
                }
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/becomeseller',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        }
        return $this->resultRedirectFactory->create()->setPath(
            'mpstripe/seller/connect/',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }

    //call delete method of obj
    public function deleteObj($object)
    {
        $object->delete();
    }
}
