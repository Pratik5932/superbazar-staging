<?php
namespace Superbazaar\PostcodeWisePrice\Plugin;

use Magento\Framework\App\PageCache\Version;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Webkul\MpHyperLocal\Helper\Data as HelperData;

class SetAddress
{

    protected $cacheTypeList;

    protected $cacheFrontendPool;

    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool,
        HelperData $helperData
    )
    {
        $this->_coreSession = $coreSession;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->helperData = $helperData;
    }

    public function afterExecute(\Webkul\MpHyperLocal\Controller\Index\SetAddress $subject, $result)
    {
        $addressData = $this->helperData->getSavedAddress();

        $beforeCustomerZipcode = $this->_coreSession->getBeforeCustomerZipcode();

        if($beforeCustomerZipcode){
            $this->_coreSession->unsBeforeCustomerZipcode();
        }

        if($addressData == 0 || ($addressData && is_array($addressData) && isset($addressData['zipcode']) && $addressData['zipcode'] != $beforeCustomerZipcode)){

            $types = array('full_page');

            foreach ($types as $type) {
                $this->cacheTypeList->cleanType($type);
            }
            foreach ($this->cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }
        }

        return $result;
    }

    public function beforeExecute(\Webkul\MpHyperLocal\Controller\Index\SetAddress $subject)
    {
        $addressData = $this->helperData->getSavedAddress();

        if($addressData && is_array($addressData) && isset($addressData['zipcode'])){
            $this->_coreSession->setBeforeCustomerZipcode($addressData['zipcode']);
        }
        else{
            $this->_coreSession->unsBeforeCustomerZipcode();
        }
    }
}