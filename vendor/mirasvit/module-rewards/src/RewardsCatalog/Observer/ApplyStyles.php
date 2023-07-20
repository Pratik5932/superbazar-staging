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



namespace Mirasvit\RewardsCatalog\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ApplyStyles implements ObserverInterface
{
    /**
     * @var \Mirasvit\Rewards\Model\Config
     */
    private $config;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var string
     */
    protected $assetName = 'Mirasvit_RewardsCheckout::css/source/module.css';

    /**
     * @var array
     */
    protected $_cssOptions =  [
        'content_type' => 'css',
        'src'          => 'Mirasvit_RewardsCheckout::css/source/module.css',
    ];

    public function __construct(
        \Mirasvit\Rewards\Model\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getData('layout');

        if ($this->config->getAdvancedApplyStyles($this->storeManager->getStore())) {
            $pageConfig = $layout->getReaderContext()->getPageConfigStructure();
            $pageConfig->addAssets($this->assetName, $this->_cssOptions);
        }
    }
}
