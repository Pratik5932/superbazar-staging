<?php

namespace Ced\Wallet\Ui\Component;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
/**
 * Class ProductActions
 */
class Wallet extends Column
{
    protected $escaper;
    protected $systemStore;
    protected $productloader;
    protected $_transactionFactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Catalog\Model\ProductFactory $productloader,
        \Ced\Wallet\Model\TransactionFactory $transactionFactory,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        $this->escaper = $escaper;
        $this->productloader = $productloader;
        $this->_transactionFactory = $transactionFactory;
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
                $transaction = $this->_transactionFactory->create()->load($item['entity_id'],'customer_id');
                $iswalletactive = __('No');
                if ($transaction && $transaction->getId()) {
                    $iswalletactive = __('Yes');
                }
                $item['enable_wallet'] = $iswalletactive;
                
            }
        }

        return $dataSource;
    }
}