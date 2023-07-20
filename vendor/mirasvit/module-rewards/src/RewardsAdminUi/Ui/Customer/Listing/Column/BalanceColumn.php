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



namespace Mirasvit\RewardsAdminUi\Ui\Customer\Listing\Column;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Mirasvit\Rewards\Helper\Balance;

class BalanceColumn extends Column
{
    private $productMetadata;
    private $rewardsBalance;

    public function __construct(
        Balance $rewardsBalance,
        ProductMetadataInterface $productMetadata,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->productMetadata = $productMetadata;
        $this->rewardsBalance  = $rewardsBalance;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                try {
                    $item[$name][0] = '<span style="color: green;display: block;text-align: right;">' .
                        $this->rewardsBalance->getBalancePoints($item['entity_id']) . '</span>'
                    ;
                } catch (NoSuchEntityException $e) {
                    $item[$name][0] = '<span style="color: red">Tier was removed. Reassign tier</span>';
                }
            }
        }
        return $dataSource;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        if ($this->getData('config/dataType') == 'int') { // export customer grid
            $config = $this->getData('config');
            $config['dataType'] = 'text';
            $this->setData('config', $config);
            $this->_data['config']['componentDisabled'] = true;
        }
        parent::prepare();
    }
}