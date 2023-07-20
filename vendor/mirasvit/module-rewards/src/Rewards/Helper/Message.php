<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rewards
 * @version   3.0.57
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rewards\Helper;

use Magento\Customer\Model\Session;
use Mirasvit\Rewards\Service\Email\VariableObjectFactory;
use Magento\Customer\Model\Url;
use Magento\Email\Model\Template\Filter;
use Mirasvit\Rewards\Helper\Data as RewardsData;
use Mirasvit\Rewards\Helper\Balance;
use Magento\Framework\App\Helper\Context;
use Mirasvit\Rewards\Helper\Referral;

class Message extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $templateFilter;

    private $variableObjectFactory;

    private $customerSession;

    private $customerUrl;

    private $rewardsData;

    private $rewardsBalance;

    private $referralHelper;

    private $context;

    public function __construct(
        Session $customerSession,
        VariableObjectFactory $variableObjectFactory,
        Url $customerUrl,
        Filter $templateFilter,
        RewardsData $rewardsData,
        Balance $rewardsBalance,
        Referral $referralHelper,
        Context $context
    ) {
        $this->templateFilter        = $templateFilter;
        $this->variableObjectFactory = $variableObjectFactory;
        $this->customerSession       = $customerSession;
        $this->customerUrl           = $customerUrl;
        $this->rewardsData           = $rewardsData;
        $this->rewardsBalance        = $rewardsBalance;
        $this->referralHelper        = $referralHelper;
        $this->context               = $context;

        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getNoteWithVariables()
    {
        $note = __('You can use the following variables:') . '<br>';
        $note .= '{{var customer.getName()}} - customer name<br>';
        $note .= '{{store url=""}} - store URL<br>';
        $note .= '{{var store.getFrontendName()}} - store name<br>';
        $note .= '{{var transaction_amount}} - formatted amount of current transaction (e.g 10 Rewards Points)<br>';
        $note .= '{{var balance_total}} - formatted balance of customer account (e.g. 100 Rewards Points)<br>';
        $note .= 'Leave empty to use default notification email. <br>';

        return $note;
    }

    /**
     * @return string
     */
    public function getNotificationNoteWithVariables()
    {
        $note = __('You can use the following variables:') . '<br>';
        $note .= '{{var customer.getName()}} - customer name<br>';
        $note .= '{{store url=""}} - store URL<br>';
        $note .= '{{var store.getFrontendName()}} - store name<br>';
        $note .= '{{var balance_total}} - formatted balance of customer account (e.g. 100 Rewards Points)<br>';

        return $note;
    }

    /**
     * @return string
     */
    public function getEarningRuleNotificationNote()
    {
        $note = __('You can use the following variables:') . '<br>';
        $note .= '{{var customer.getName()}} - customer name<br>';
        $note .= '{{store url=""}} - store URL<br>';
        $note .= '{{var store.getFrontendName()}} - store name<br>';
        $note .= '{{var balance_total}} - formatted balance of customer account (e.g. 100 Rewards Points)<br>';

        return $note;
    }

    /**
     * @param string $message
     *
     * @return string
     */
    public function processNotificationVariables($message)
    {
        $customer         = $this->getCustomer();
        $customerVariable = $this->getVariableObject($customer);

        $this->templateFilter->setVariables([
            'customer'      => $customerVariable,
            'store'         => $customer->getStore(),
            'balance_total' => $this->rewardsData->formatPoints($this->rewardsBalance->getBalancePoints($customer)),
        ]);

        return $this->templateFilter->filter((string)$message);
    }

    /**
     * @param string $message
     *
     * @return string
     */
    public function processProductNotificationVariables($message)
    {
        $customer         = $this->getCustomer();
        $customerVariable = $this->getVariableObject($customer);

        $this->templateFilter->setVariables([
            'customer'      => $customerVariable,
            'store'         => $customer->getStore(),
            'balance_total' => $this->rewardsData->formatPoints($this->rewardsBalance->getBalancePoints($customer)),
        ]);

        return $this->templateFilter->filter((string)$message);
    }

    /**
     * @param string $message
     *
     * @return string
     */
    public function processCheckoutNotificationVariables($message)
    {
        $customer         = $this->getCustomer();
        $customerVariable = $this->getVariableObject($customer);

        $this->templateFilter->setVariables([
            'customer'                => $customerVariable,
            'store'                   => $customer->getStore(),
            'balance_total'           => $this->rewardsBalance->getBalancePoints($customer),
            'balance_total_formatted' => $this->rewardsData->formatPoints(
                $this->rewardsBalance->getBalancePoints($customer)
            ),
        ]);

        return $this->templateFilter->filter((string)$message);
    }

    /**
     * @param string $message
     *
     * @return string
     */
    public function processReferralInvitationVariables($message)
    {
        $customer         = $this->getCustomer();
        $customerVariable = $this->getVariableObject($customer);
        $this->templateFilter->setVariables([
            'customer'       => $customerVariable,
            'invitation_url' => $this->context->getUrlBuilder()->getUrl('r/' . $this->referralHelper->getReferralLinkId()),
        ]);

        return $this->templateFilter->filter((string)$message);
    }

    /**
     * Get logged in customer.
     * @return \Magento\Customer\Model\Customer|\Magento\Customer\Model\Customer
     */
    protected function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }

    /**
     * @param object $object
     *
     * @return \Magento\Framework\DataObject
     */
    protected function getVariableObject($object)
    {
        $objectVariable = $this->variableObjectFactory->create();
        $objectVariable->setCoreObject($object);

        return $objectVariable;
    }
}
