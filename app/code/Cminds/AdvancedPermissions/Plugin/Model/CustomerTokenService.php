<?php

namespace Novus\Suppliers\Plugin\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Model\CredentialsValidator;
use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory as TokenCollectionFactory;

/**
 * Class CustomerTokenService
 *
 * @package Novus\Suppliers\Plugin\Model
 */
class CustomerTokenService extends \Magento\Integration\Model\CustomerTokenService
{
    /**
     * Token Collection Factory
     *
     * @var TokenCollectionFactory
     */
    private $tokenModelCollectionFactory;

    /**
     * Initialize service
     *
     * @param TokenCollectionFactory $tokenModelCollectionFactory
     */
    public function __construct(
        TokenModelFactory $tokenModelFactory,
        AccountManagementInterface $accountManagement,
        TokenCollectionFactory $tokenModelCollectionFactory,
        \Magento\Integration\Model\CredentialsValidator $validatorHelper
    ) {
        parent::__construct(
            $tokenModelFactory,
            $accountManagement,
            $tokenModelCollectionFactory,
            $validatorHelper
        );
        $this->tokenModelCollectionFactory = $tokenModelCollectionFactory;
    }

    public function revokeCustomerAccessToken($customerId)
    {
        $tokenCollection = $this->tokenModelCollectionFactory->create()->addFilterByCustomerId($customerId);
        if ($tokenCollection->getSize() == 0) {
            return true;
        }
        try {
            foreach ($tokenCollection as $token) {
                $token->delete();
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__('The tokens could not be revoked.'));
        }
        return true;
    }
}
