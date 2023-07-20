<?php
namespace Mexbs\AdditionalPromotions\Controller\Adminhtml\Promo\Quote;

class NewActionDetailsHtml extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote
{
    protected $apHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Mexbs\AdditionalPromotions\Helper\Data $apHelper
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $dateFilter
        );
        $this->apHelper = $apHelper;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $subPrefix = $this->getRequest()->getParam('sub_prefix');

        $simpleAction = $this->getRequest()->getParam('simple_action');
        if($simpleAction){
            $type = $this->apHelper->getSimpleActionType($simpleAction);
        }else{
            $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
            $type = $typeArr[0];
        }

        $model = $this->_objectManager->create(
            $type
        )->setId(
            $id
        )->setType(
            $type
        )->setRule(
            $this->_objectManager->create('Magento\SalesRule\Model\Rule')
        )->setPrefix(
           'action_details'
        )->setSubPrefix(
           $subPrefix
        )->setActionDetails([]);

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof \Magento\Rule\Model\Condition\AbstractCondition) {
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
}
