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


/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mirasvit\RewardsBehavior\Observer;

use Magento\Framework\Event\ObserverInterface;

class ReferralRedirectOnCmsRouteMatch implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->url = $url;
        $this->request = $request;
    }

    /**
     * Modify No Route Forward object.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        \Magento\Framework\Profiler::start(__CLASS__.':'.__METHOD__);
        $request = $this->request;
        $pathInfo = $request->getPathInfo();
        $identifier = trim((string) $pathInfo, '/');
        $parts = explode('/', $identifier);
        if (count($parts) == 2 && $parts[0] == 'r') {
            $observer
                ->getEvent()
                ->getCondition()
                ->setRedirectUrl($this->url->getUrl('rewards_behavior/referral/referralVisit/referral_link/'.$parts[1]));
        }
        \Magento\Framework\Profiler::stop(__CLASS__.':'.__METHOD__);
    }
}
