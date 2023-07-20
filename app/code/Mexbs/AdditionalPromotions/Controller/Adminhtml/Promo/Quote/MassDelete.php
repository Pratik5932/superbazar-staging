<?php
namespace Mexbs\AdditionalPromotions\Controller\Adminhtml\Promo\Quote;

use Magento\Framework\Controller\ResultFactory;

class MassDelete extends \Magento\Backend\App\Action
{
    protected $rulesCollectionFactory;


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $rulesCollectionFactory
    ){
        $this->rulesCollectionFactory = $rulesCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $ruleIds = $this->getRequest()->getParam('rule_id');
        if (!$ruleIds || !is_array($ruleIds)) {
            throw new \Exception(__('No rules selected.'));
        }

        $rulesCollection = $this->rulesCollectionFactory->create()
            ->addFieldToFilter("rule_id", ["in" => $ruleIds]);
        $collectionSize = $rulesCollection->getSize();

        foreach ($rulesCollection as $rule) {
            $rule->delete();
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('sales_rule/promo_quote/index');
    }
}
