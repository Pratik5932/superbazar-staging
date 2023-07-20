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



namespace Mirasvit\RewardsAdminUi\Model\System\Config\Source\Email\Template;

class Tier extends \Magento\Config\Model\Config\Source\Email\Template
{
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory,
        \Magento\Email\Model\Template\Config $emailConfig,
        array $data = []
    ) {
        $data['path'] = 'rewards/email/tier_up';
        parent::__construct($coreRegistry, $templatesFactory, $emailConfig, $data);
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        array_unshift($options,
            [
                 'value' => 'none',
                 'label' => __('- Disable these emails -'),
            ]);

            return $options;
    }

    /************************/
}
