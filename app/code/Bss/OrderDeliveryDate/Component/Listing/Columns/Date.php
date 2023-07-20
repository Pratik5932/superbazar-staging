<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_OrderDeliveryDate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OrderDeliveryDate\Component\Listing\Columns;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class Date
 *
 * @package Bss\OrderDeliveryDate\Component\Listing\Columns
 */
class Date extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Bss\OrderDeliveryDate\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\OrderDeliveryDate\Helper\ConvertDate
     */
    protected $helperDate;

    /**
     * Date constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param TimezoneInterface $timezone
     * @param \Bss\OrderDeliveryDate\Helper\Data $helper
     * @param \Bss\OrderDeliveryDate\Helper\ConvertDate $helperDate
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TimezoneInterface $timezone,
        \Bss\OrderDeliveryDate\Helper\Data $helper,
        \Bss\OrderDeliveryDate\Helper\ConvertDate $helperDate,
        array $components = [],
        array $data = []
    ) {
        $this->timezone = $timezone;
        $this->helper = $helper;
        $this->helperDate = $helperDate;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if (isset($config['filter'])) {
            $config['filter'] = [
                'filterType' => 'dateRange',
                'templates' => [
                    'date' => [
                        'options' => [
                            'dateFormat' => $this->timezone->getDateFormatWithLongYear()
                        ]
                    ]
                ]
            ];
        }
        $this->setData('config', $config);

        parent::prepare();
    }

    /**
     * Prepare date column on sales order grid
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])) {
                    $date = $item[$this->getData('name')];
                    $item[$this->getData('name')] = $this->timezone->scopeDate(null, $date, true)
                        ->format($this->helper->formatDate());
                }
            }
        }

        return $dataSource;
    }
}
