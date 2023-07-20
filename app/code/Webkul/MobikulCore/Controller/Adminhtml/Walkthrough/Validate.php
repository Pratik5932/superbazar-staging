<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Controller\Adminhtml\Walkthrough;

/**
 * Class Validate for walkthrough
 */
class Validate extends \Webkul\MobikulCore\Controller\Adminhtml\Walkthrough
{
    /**
     * Function to validate Images
     *
     * @return array
     */
    protected function _validateImage($response)
    {
        $walkthrough = null;
        $errors = [];
        try {
            $walkthrough = $this->walkthroughDataFactory->create();
            $data = $this->getRequest()->getParams();
            $dataResult = $data["mobikul_walkthrough"];
            $errors = [];
            if (!isset($dataResult["image"][0]["name"])) {
                $errors[] = __("Please upload walk through image.");
            }
            if ($dataResult["title"] == "") {
                $errors[] = __("Title can not be blank.");
            }
            if (strlen($dataResult["title"]) > 50) {
                $errors[] = __("Title can not be greater than 50 characters.");
            }
            if ($dataResult["description"] == "") {
                $errors[] = __("Description can not be blank.");
            }
            if (strlen($dataResult["description"]) > 200) {
                $errors[] = __("Description can not be greater than 200 characters.");
            }
        } catch (\Magento\Framework\Validator\Exception $exception) {
            $exceptionMsg = $exception->getMessages(\Magento\Framework\Message\MessageInterface::TYPE_ERROR);
            foreach ($exceptionMsg as $error) {
                $errors[] = $error->getText();
            }
        }
        if ($errors) {
            $messages = $response->hasMessages() ? $response->getMessages() : [];
            foreach ($errors as $error) {
                $messages[] = $error;
            }
            $response->setMessages($messages);
            $response->setError(1);
        }
        return $walkthrough;
    }

    /**
     * Execute Function for Class Validate
     *
     * @return jSon
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(0);
        $this->_validateImage($response);
        $resultJson = $this->resultJsonFactory->create();
        if ($response->getError()) {
            $response->setError(true);
            $response->setMessages($response->getMessages());
        }
        $resultJson->setData($response);
        return $resultJson;
    }
}
