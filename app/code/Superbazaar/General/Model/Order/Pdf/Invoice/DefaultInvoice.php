<?php
/**
* Copyright © Magento, Inc. All rights reserved.
* See COPYING.txt for license details.
*/
declare(strict_types=1);

namespace Superbazaar\General\Model\Order\Pdf\Invoice;

/**
* Sales Order Invoice Pdf default items renderer
*/
class DefaultInvoice extends \Magento\Sales\Model\Order\Pdf\Items\Invoice\DefaultInvoice
{
    /**
    * Core string
    *
    * @var \Magento\Framework\Stdlib\StringUtils
    */


    /**
    * Draw item line
    *
    * @return void
    */
    public function draw()
    {
        $order = $this->getOrder();
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $lines = [];

        // draw Product name
        $lines[0] = [
            [
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                'text' => $this->string->split(html_entity_decode($item->getName()), 40, true, true),
                'feed' => 35
            ]
        ];
        $lines[0][] = ['text' => $item->getQty() * 1, 'feed' => 250, 'align' => 'right'];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());

        $lines[0][] = ['text' =>$product->getAttributeText('aisle'), 'feed' => 290, 'align' => 'right'];
        // draw SKU
        $sku =$this->getSku($item);
        $lines[0][] = [
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            'text' => $this->string->split(html_entity_decode(mb_substr($sku,0,12)), 30),
            'feed' => 400,
            'align' => 'left',
        ];

        // draw QTY


        // draw item Prices
        $i = 0;
        $prices = $this->getItemPricesForDisplay();
        $feedPrice = 395;
        $feedSubtotal = $feedPrice + 170;
        foreach ($prices as $priceData) {
            if (isset($priceData['label'])) {
                // draw Price label
                $lines[$i][] = ['text' => $priceData['label'], 'feed' => 350, 'align' => 'left'];
                // draw Subtotal label
                $lines[$i][] = ['text' => $priceData['label'], 'feed' => $feedSubtotal, 'align' => 'left'];
                $i++;
            }
            // draw Price
            $lines[$i][] = [
                'text' => $priceData['price'],
                'feed' => 350,
                'font' => 'bold',
                'align' => 'right',
            ];
            // draw Subtotal
            $lines[$i][] = [
                'text' => $priceData['subtotal'],
                'feed' => $feedSubtotal,
                'font' => 'bold',
                'align' => 'right',
            ];
            $i++;
        }

        // draw Tax
        $lines[0][] = [
            'text' => $order->formatPriceTxt($item->getTaxAmount()),
            'feed' => 495,
            'font' => 'bold',
            'align' => 'right',
        ];

        // custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = [
                    'text' => $this->string->split($this->filterManager->stripTags($option['label']), 40, true, true),
                    'font' => 'italic',
                    'feed' => 35,
                ];

                // Checking whether option value is not null
                if ($option['value'] !== null) {
                    if (isset($option['print_value'])) {
                        $printValue = $option['print_value'];
                    } else {
                        $printValue = $this->filterManager->stripTags($option['value']);
                    }
                    $values = explode(', ', $printValue);
                    foreach ($values as $value) {
                        $lines[][] = ['text' => $this->string->split($value, 30, true, true), 'feed' => 40];
                    }
                }
            }
        }

        $lineBlock = ['lines' => $lines, 'height' => 20];

        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $this->setPage($page);
    }
}
