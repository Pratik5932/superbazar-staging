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
use Mirasvit\Rewards\Api\Repository\TierRepositoryInterface;

class TierColumn extends Column
{
    private $productMetadata;
    private $tierRepository;

    public function __construct(
        ProductMetadataInterface $productMetadata,
        TierRepositoryInterface $tierRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->productMetadata = $productMetadata;
        $this->tierRepository = $tierRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (!empty($item[$name][0])) {
                    try {
                        $tier = $this->tierRepository->get($item[$name][0]);
                        $item[$name][0] = $tier->getName();
                    } catch (NoSuchEntityException $e) {
                        $item[$name][0] = '<span style="color: red">Tier was removed. Reassign tier</span>';
                    }
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
            $config['dataType'] = 'select';
            $this->setData('config', $config);
        }
        parent::prepare();
        if (!version_compare($this->productMetadata->getVersion(), "2.2.4", ">=")) {
            $this->_data['config']['componentDisabled'] = true;
        }
    }
}