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



namespace Mirasvit\RewardsAdminUi\Ui\Tier\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Email\Model\Template\Config;
use Magento\Email\Model\TemplateFactory;
use Mirasvit\Rewards\Api\Data\TierInterface;
use Mirasvit\Rewards\Helper\Storeview;

class Template extends Column
{
    private $emailConfig;
    private $templateFactory;
    private $helpdeskStoreview;

    public function __construct(
        Storeview $helpdeskStoreview,
        Config $emailConfig,
        TemplateFactory $templateFactory,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->emailConfig = $emailConfig;
        $this->templateFactory = $templateFactory;
        $this->helpdeskStoreview = $helpdeskStoreview;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$this->getData('name')])) {
                    if ($this->getData('name') == TierInterface::KEY_TEMPLATE_ID) {
                        $object = new \Magento\Framework\DataObject($item);
                        $item[$this->getData('name')] = $this->helpdeskStoreview
                            ->getStoreViewValue($object, $this->getData('name'));
                    }
                    $item[$this->getData('name')] = $this->getTemplateName($item[$this->getData('name')]);
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get customer name
     *
     * @param int $templateId
     * @return string
     */
    public function getTemplateName($templateId)
    {
        if ($templateId === 'none') {
            $templateLabel = '';
        } elseif (!(int)$templateId) {
            $templateId = 'rewards_email_tier_up';
            $templateLabel = $this->emailConfig->getTemplateLabel($templateId);
        } else {
            $template = $this->templateFactory->create();
            $template->getResource()->load($template, $templateId);
            $templateLabel = $template->getTemplateCode();
        }

        return $templateLabel;
    }
}
