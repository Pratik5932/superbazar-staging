<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpStripe\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Accounts extends Column
{
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Webkul\MpStripe\Helper\Data $helper,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->helper = $helper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['entity_id'])) {
                    $integrationType = $this->helper->getIntegration();
                    if ($integrationType) {
                        $item[$this->getData('name')] = [
                            'view' => [
                                'href' => '#',
                                'label' => __('Stripe Connect'),
                            ],
                        ];
                    } else {
                        $viewUrlPath = $this->getData('config/viewUrlPath') ?: '#';
                        $urlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'seller_id';
                        $item[$this->getData('name')] = [
                            'view' => [
                                'href' => $this->_urlBuilder->getUrl(
                                    $viewUrlPath,
                                    [
                                        $urlEntityParamName => $item['seller_id'],
                                    ]
                                ),
                                'label' => __('Manage Stripe Connect Custom Account'),
                            ],
                        ];
                    }
                }
            }
        }

        return $dataSource;
    }
}
