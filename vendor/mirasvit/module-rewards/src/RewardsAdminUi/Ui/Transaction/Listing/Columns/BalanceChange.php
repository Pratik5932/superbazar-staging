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



namespace Mirasvit\RewardsAdminUi\Ui\Transaction\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class BalanceChange extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
                if (isset($item[$this->getData('name')]) && $item[$this->getData('name')] > 0) {
                    $item[$this->getData('name')] =  $this->prepareItem('+' . $item[$this->getData('name')], true);
                } elseif (isset($item[$this->getData('name')]) && $item[$this->getData('name')] < 0) {
                    $item[$this->getData('name')] =  $this->prepareItem($item[$this->getData('name')], false);
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param string $item
     * @param bool $isPositive
     * @return string
     */
    protected function prepareItem($item, $isPositive)
    {
        $class = ($isPositive) ? 'green' : 'red';
        return '<span class="' . $class . '">' . $item . '</span>';
    }
}
