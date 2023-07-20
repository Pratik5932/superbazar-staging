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



namespace Mirasvit\RewardsCustomerAccount\Block\Account;

use Mirasvit\Rewards\Service\MenuLink;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Html\Link\Current;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\DefaultPathInterface;

class AccountLink extends Current
{
    protected $menuLink;

    private   $customerSession;

    public function __construct(
        MenuLink $menuLink,
        Session $customerSession,
        Context $context,
        DefaultPathInterface $defaultPath,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);

        $this->menuLink        = $menuLink;
        $this->customerSession = $customerSession;
    }

    /**
     * Check if the link should be shown
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        $html = '';

        if ($this->menuLink->isShowMenuForCurrentCustomer($this->customerSession->getCustomer())) {
            $html = parent::_toHtml();
        }

        return $html;
    }


}
