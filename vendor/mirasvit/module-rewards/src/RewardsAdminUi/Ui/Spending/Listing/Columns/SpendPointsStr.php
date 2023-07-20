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



namespace Mirasvit\RewardsAdminUi\Ui\Spending\Listing\Columns;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mirasvit\Rewards\Api\Data\Spending\RuleInterface;
use Mirasvit\Rewards\Api\Repository\TierRepositoryInterface;
use Mirasvit\Rewards\Helper\Json;
use Mirasvit\Rewards\Helper\Data;

class SpendPointsStr extends Column
{
    private $dataHelper;
    private $jsonHelper;
    private $tierRepository;
    /**
     * @var array
     */
    protected $tiers = [];

    public function __construct(
        Data $dataHelper,
        Json $jsonHelper,
        TierRepositoryInterface $tierRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->dataHelper = $dataHelper;
        $this->jsonHelper = $jsonHelper;
        $this->tierRepository = $tierRepository;
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
                if (!empty($item[RuleInterface::KEY_TIERS_SERIALIZED])) {
                    $data = $this->jsonHelper->unserialize($item[RuleInterface::KEY_TIERS_SERIALIZED]);
                    $str = '';
                    foreach ($data as $tierId => $settings) {
                        $tier = $this->getTier($tierId);
                        $points = $this->dataHelper->formatPoints($settings[RuleInterface::KEY_SPEND_POINTS]);
                        $str .= '<b>' . $tier->getName() . '</b>:<br/>';
                        $str .= '&nbsp;&nbsp;&nbsp;' . __('%1 for each $%2',
                                $points,
                                $settings[RuleInterface::KEY_MONETARY_STEP]
                            ) . '<br/>';
                    }
                    $item[$this->getData('name')] = $str;
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param int $id
     * @return \Mirasvit\Rewards\Api\Data\TierInterface
     */
    protected function getTier($id)
    {
        if (!isset($this->tiers[$id])) {
            try {
                $tier = $this->tierRepository->get($id);
            } catch (NoSuchEntityException $e) {
                $tier = new \Magento\Framework\DataObject();
                $tier->setName('<span style="color: red;">'.$id.'</span>');
            }
            $this->tiers[$id] = $tier;
        }

        return $this->tiers[$id];
    }
}
