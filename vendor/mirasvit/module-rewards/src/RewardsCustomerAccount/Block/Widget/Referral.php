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



namespace Mirasvit\RewardsCustomerAccount\Block\Widget;

use Magento\Framework\App\Http\Context as Auth;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Mirasvit\Rewards\Model\Config;
use Magento\Customer\Model\CustomerFactory;
use Mirasvit\Rewards\Service\Widget;
use Mirasvit\Rewards\Helper\Referral as ReferralHelper;
use Magento\Framework\View\Element\Template\Context;

class Referral extends Template implements BlockInterface
{
    protected $_template = "widget/referral.phtml";

    protected $customerFactory;

    protected $widget;

    protected $referralHelper;

    public    $auth;

    protected $config;

    protected $context;

    public function __construct(
        Config $config,
        CustomerFactory $customerFactory,
        Widget $widget,
        ReferralHelper $referralHelper,
        Auth $auth,
        Context $context,
        array $data = []
    ) {
        $this->config          = $config;
        $this->customerFactory = $customerFactory;
        $this->widget          = $widget;
        $this->referralHelper  = $referralHelper;
        $this->auth            = $auth;
        $this->context         = $context;

        parent::__construct($context, $data);
        $this->setTemplate($this->_template);
    }

    /**
     * @return bool
     */
    public function getAddThisScript()
    {
        return $this->widget->getAddThisWidgetCode();
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->auth->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * @return string
     */
    public function getShareUrl()
    {
        return $this->context->getUrlBuilder()->getUrl('r/' . $this->referralHelper->getReferralLinkId($this->getCustomerId()));
    }

    /**
     * @return string
     */
    public function getHomeUrl()
    {
        return $this->context->getUrlBuilder()->getUrl();
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->auth->getValue('customer_id');
    }
}
