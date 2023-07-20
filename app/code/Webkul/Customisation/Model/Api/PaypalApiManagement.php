<?php

namespace Webkul\Customisation\Model\Api;

class PaypalApiManagement implements \Webkul\Customisation\Api\PaypalApiManagementInterface
{
    const SEVERE_ERROR = 0;
    const SUCCESS = 1;
    const LOCAL_ERROR = 2;

    protected $indexController;
    protected $resultJsonFactory;

    public function __construct(
        \Webkul\Customisation\Controller\Index $indexController,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory

    ) {
        $this->indexController = $indexController;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * get test Api data.
     *
     * @api
     *
     * @param $id
     * @return \Webkul\TestApi\Api\Data\TestApiInterface
     */
    public function getApiData($id)
    {
        try {
            $data = [];
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $checkoutSession = $objectManager->create(\Magento\Checkout\Model\Session::class);
            $checkoutSession->setQuoteIdForUrl($id);
            $url = $this->indexController->execute();
            if ($url) {
                $data = [
                    'url' => $url
                ];
            }
            $checkoutSession->unsQuoteIdForUrl();
            $checkoutSession->unsQuote();
            return $data;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = 0;
            return $returnArray;
        }
    }
}