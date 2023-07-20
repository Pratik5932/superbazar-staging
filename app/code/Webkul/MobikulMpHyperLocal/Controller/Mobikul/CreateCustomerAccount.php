<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulMpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpYapay\Controller\Mobikul;

use Webkul\MobikulApi\Controller\Customer\CreateAccount;
use Magento\Framework\App\ObjectManager;

class CreateCustomerAccount extends CreateAccount
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $emailValidator = new \Zend\Validator\EmailAddress();
            if (!$emailValidator->isValid($this->email)) {
                $this->returnArray["message"] = __("Invalid email address.");
                return $this->getJsonResponse($this->returnArray);
            }
            $this->customer = $this->customerFactory->create()->setWebsiteId($this->websiteId)->loadByEmail($this->email);
            $this->customerId = $this->customer->getId();
            if ($this->isSocial == 1 && $this->customerId > 0) {
                $confirmationStatus = $this->accountManagement->getConfirmationStatus($this->customerId);
                if ($confirmationStatus === \Magento\Customer\Api\AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                    $this->returnArray["message"] = __("You must confirm your account. Please check your email for the confirmation link");
                    return $this->getJsonResponse($this->returnArray);
                }
                $this->returnArray["success"] = true;
                $this->returnArray["message"] = __("Your are now Loggedin");
                $this->returnArray["customerName"] = $this->customer->getName();
                $this->returnArray["customerEmail"] = $this->customer->getEmail();
                $this->returnArray["customerToken"] = $this->helper->getTokenByCustomerDetails($this->email, $this->password, $this->customerId);
                $this->getCustomerImages();
                $this->tokenHelper->saveToken($this->customerId, $this->token, $this->os);
                return $this->getJsonResponse($this->returnArray);
            } else {
                if ($this->customerId > 0) {
                    $this->returnArray["message"] = __("There is already an account with this email address.");
                    return $this->getJsonResponse($this->returnArray);
                }
            }
            $this->customer = $this->customerFactory->create();
            $customerData = [
                "dob" => $this->dob,
                "email" => $this->email,
                "prefix" => $this->prefix,
                "suffix" => $this->suffix,
                "taxvat" => $this->taxvat,
                "gender" => $this->gender,
                "lastname" => $this->lastName,
                "password" => $this->password,
                "firstname" => $this->firstName,
                "website_id" => $this->websiteId,
                "middlename" => $this->middleName,
                "group_id" => $this->helper->getConfigData(\Magento\Customer\Model\GroupManagement::XML_PATH_DEFAULT_ID)
            ];
            $this->getRequest()->setParams($customerData);

            $address = $this->extractAddress();
            $addresses = $address === null ? [] : [$address];
            $customerObject = $this->customerExtractor->extract("customer_account_create", $this->_request);
            $customerObject->setAddresses($addresses);
            // Creating Customer ////////////////////////////////////////////////////
            $this->customer = $this->accountManagement->createAccount($customerObject, $this->password, "");
            $this->customerId = $this->customer->getId();
            $this->customer = $this->customerFactory->create()->load($this->customerId);
            // Setting Social Data //////////////////////////////////////////////////
            if ($this->isSocial == 1) {
                $this->doSocialLogin();
            }
            $this->returnArray["customerName"] = $this->customer->getName();
            $this->customer = $this->customerRepositoryInterface->getById($this->customerId);
            $this->mergeQuote();
            $confirmationStatus = $this->accountManagement->getConfirmationStatus($this->customer->getId());
            if ($confirmationStatus === \Magento\Customer\Api\AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                $this->returnArray["message"] = __("You must confirm your account. Please check your email for the confirmation link");
                return $this->getJsonResponse($this->returnArray);
            }
            $quote = $this->helper->getCustomerQuote($this->customerId);

            $eventManager = ObjectManager::getInstance()->get(\Magento\Framework\Event\ManagerInterface::class);
            $eventManager->dispatch(
                'customer_register_success',
                ['account_controller' => $this, 'customer' => $this->customer]
            );
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Your Account has been successfully created");
            $this->returnArray["cartCount"] = $quote->getItemsQty() * 1;
            $this->returnArray["customerEmail"] = $this->email;
            $this->returnArray["customerId"] = $this->customerId;
            $this->returnArray["customerToken"] = $this->helper->getTokenByCustomerDetails($this->email, $this->password, $this->customerId);
            $this->tokenHelper->saveToken($this->customerId, $this->token, $this->os);
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    protected function extractAddress()
    {
        $formFactory = ObjectManager::getInstance()->get(\Magento\Customer\Model\Metadata\FormFactory::class);
        $regionDataFactory = ObjectManager::getInstance()->get(\Magento\Customer\Api\Data\RegionInterfaceFactory::class);
        $addressDataFactory = ObjectManager::getInstance()->get(\Magento\Customer\Api\Data\AddressInterfaceFactory::class);
        $dataObjectHelper = ObjectManager::getInstance()->get(\Magento\Framework\Api\DataObjectHelper::class);

        $addressForm = $formFactory->create('customer_address', 'customer_register_address');
        $allowedAttributes = $addressForm->getAllowedAttributes();

        $addressData = [];

        $regionDataObject = $regionDataFactory->create();
        foreach ($allowedAttributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $value = $this->getRequest()->getParam($attributeCode);
            if ($value === null) {
                continue;
            }
            switch ($attributeCode) {
                case 'region_id':
                    $regionDataObject->setRegionId($value);
                    break;
                case 'region':
                    $regionDataObject->setRegion($value);
                    break;
                default:
                    $addressData[$attributeCode] = $value;
            }
        }
        $addressDataObject = $addressDataFactory->create();
        $dataObjectHelper->populateWithArray(
            $addressDataObject,
            $addressData,
            \Magento\Customer\Api\Data\AddressInterface::class
        );
        $addressDataObject->setRegion($regionDataObject);

        $addressDataObject->setIsDefaultBilling(true)->setIsDefaultShipping(true);
        return $addressDataObject;
    }
}