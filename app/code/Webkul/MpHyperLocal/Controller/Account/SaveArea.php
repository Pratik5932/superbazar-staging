<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpHyperLocal\Controller\Account;

use Magento\Framework\App\Action\Context;
use Webkul\MpHyperLocal\Model\ShipAreaFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

class SaveArea extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Webkul\MpHyperLocal\Model\ShipAreaFactory
     */
    private $shipAreaFactory;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param ShipAreaFactory $shipAreaFactory
     * @param FormKeyValidator $formKeyValidator
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        ShipAreaFactory $shipAreaFactory,
        FormKeyValidator $formKeyValidator
    ) {
        $this->customerSession  = $customerSession;
        $this->shipAreaFactory  = $shipAreaFactory;
        $this->formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }

    /**
     * Ship area save
     * @return \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath(
                'mphyperlocal/account/addshiparea',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
        $sellerId = $this->customerSession->getCustomerId();
        
        $data = $this->getRequest()->getParams();
        if ($data && $data['latitude'] !='' && $data['longitude'] !='') {
            $shipArea = $this->shipAreaFactory->create()->getCollection()
                                ->addFieldToFilter('latitude', $data['latitude'])
                                ->addFieldToFilter('longitude', $data['longitude'])
                                ->addFieldToFilter('seller_id', $sellerId)->setPageSize(1)->getFirstItem();
            if (!$shipArea->getEntityId()) {
                $data['postcode'] = '-';
                $shipArea = $this->shipAreaFactory->create();
                $data['seller_id'] = $sellerId;
                $shipArea->setData($data);
                $shipArea->save();
                $this->messageManager->addSuccess(__('Ship area saved successfully.'));
            } else {
                $this->messageManager->addError(__('Ship area already exist.'));
            }
        }elseif ($data && $data['postcode'] !='') {
            $shipArea = $this->shipAreaFactory->create()->getCollection()
                        ->addFieldToFilter('seller_id', $sellerId)
                        ->addFieldToFilter('address_type', $data['address_type'])
                        ->addFieldToFilter('postcode', $data['postcode'])->setPageSize(1)->getFirstItem();
            if (!$shipArea->getEntityId()) {
                $data['latitude'] = '-';
                $data['longitude'] = '-';
                $data['address'] = '-';
                $shipArea = $this->shipAreaFactory->create();
                $data['seller_id'] = $sellerId;
                $shipArea->setData($data);
                $shipArea->save();
                $this->messageManager->addSuccess(__('Ship area saved successfully.'));
            } else {
                $this->messageManager->addError(__('Ship area already exist.'));
            }
        } else {
            $this->messageManager->addError(__('Invalid request.'));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setUrl($this->_url->getUrl('mphyperlocal/account/addshiparea'));
    }
}
