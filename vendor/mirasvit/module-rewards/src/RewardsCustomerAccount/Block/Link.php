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



namespace Mirasvit\RewardsCustomerAccount\Block;

use Magento\Customer\Model\Session;
use Mirasvit\Rewards\Helper\Data;
use Mirasvit\Rewards\Service\MenuLink;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Http\Context as Auth;

/**
 * Added rewards link to top menu(customer account menu)
 */
class Link extends \Magento\Framework\View\Element\Html\Link
{
    private   $helper;

    private   $menuLink;

    private   $customerSession;

    public    $auth;

    protected $_template = 'Mirasvit_RewardsCustomerAccount::link.phtml';

    public function __construct(
        Data $helper,
        MenuLink $menuLink,
        Session $customerSession,
        Context $context,
        Auth $auth,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->helper          = $helper;
        $this->menuLink        = $menuLink;
        $this->customerSession = $customerSession;
        $this->auth            = $auth;
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl('rewards/account');
    }

    /**
     * @return bool
     */
    public function getIsLoggedIn()
    {
        return $this->auth->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * @return bool
     */
    public function isShowMenuForCurrentCustomer()
    {
        return $this->menuLink->isShowMenuForCurrentCustomer($this->customerSession->getCustomer());
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return $this->helper->getPointsName();
    }
}
