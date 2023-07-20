<?php
/**
 * @copyright Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fooman\PrintOrderPdf\Model\Pdf;

use Magento\Sales\Model\Order\Pdf\Invoice;


class Order extends Invoice
{
    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $appEmulation;

    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\Order\Pdf\Config $pdfConfig,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\App\Emulation $appEmulation,
        array $data = []
    ) {
        $this->appEmulation = $appEmulation;
        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfConfig,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $addressRenderer,
            $storeManager,
            $appEmulation,
            $data
        );
    }

    /**
     * Return PDF document
     *
     * @param  \Magento\Sales\Model\Order[] $orders
     *
     * @return \Zend_Pdf
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Pdf_Exception
     */

    protected function _setFontRegular($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/open-sans/OpenSans-Regular.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontBold($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/open-sans/OpenSans-Bold.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }
    protected function _setFontItalic($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/open-sans/OpenSans-Italic.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        //columns headers
        $lines[0][] = ['text' => __('Products'), 'feed' => 35];

        $lines[0][] = ['text' => __('Qty'), 'feed' => 250, 'align' => 'right'];

        $lines[0][] = ['text' => __('Aisle'), 'feed' => 290, 'align' => 'right'];

        $lines[0][] = ['text' => __('SKU'), 'feed' => 400, 'align' => 'center'];


        $lines[0][] = ['text' => __('Price'), 'feed' => 350, 'align' => 'right'];

        $lines[0][] = ['text' => __('Tax'), 'feed' => 495, 'align' => 'right'];

        $lines[0][] = ['text' => __('Subtotal'), 'feed' => 565, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 5];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    public function getPdf($orders = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('order');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);

        foreach ($orders as $order) {
            if ($order->getStoreId()) {
                $this->appEmulation->startEnvironmentEmulation(
                    $order->getStoreId(),
                    \Magento\Framework\App\Area::AREA_FRONTEND,
                    true
                );
                // $this->_localeResolver->emulate($order->getStoreId());
                // $this->_storeManager->setCurrentStore($order->getStoreId());
            }
            $page = $this->newPage();
            $this->_setFontBold($page, 10);
            $order->setOrder($order);
            /* Add image */
            $this->insertLogo($page, $order->getStore());
            
            /* Add address */
            $this->insertAddress($page, $order->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
            /* Add table */

            $this->_drawHeader($page);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
            $frozenCategirylist = $scopeConfig->getValue("invoicepdfsection/invoicepdfgroup/frozed_cat_ids",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $frozenCategirylist = explode(',', $frozenCategirylist);
            $freshCategirylist = $scopeConfig->getValue("invoicepdfsection/invoicepdfgroup/fresh_cat_ids",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $freshCategirylist=explode(',', $freshCategirylist);
            $mergeArray = array_merge($frozenCategirylist,$freshCategirylist);
            $frozenitemarray = array();
            $freshitemarray = array();
            $otheritems = array();
            $heading = "";
            $heading1 = "";

            $newitems = array();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            foreach ($order->getAllItems() as $_item) :
                $product = $objectManager->create('Magento\Catalog\Model\Product')->load($_item->getProductId());
                $alies = $product->getAttributeText('aisle');
                $newitems[$alies][] = $_item; 

                endforeach;
            ksort($newitems);
            $allItems = array();
            foreach ($newitems as $name => $items) {
                $allItems = array_merge($allItems, $items);
            }
            foreach ($allItems as $_item): 
                $product = $objectManager->create('Magento\Catalog\Model\Product')->load($_item->getProductId());
                $categoryCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
                $categoryIds = $product->getCategoryIds();
                $otherit = array_intersect($mergeArray,$categoryIds);
                if(!count($otherit)){
                    $otheritems[] = $_item->getProductId();
                }
                endforeach;

            foreach ($frozenCategirylist as $_frozenitem){
                foreach ($allItems as $_item): 
                    $product = $objectManager->create('Magento\Catalog\Model\Product')->load($_item->getProductId());
                    $categoryCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
                    $categoryIds = $product->getCategoryIds();
                    if(in_array($_frozenitem,$categoryIds)){
                        $frozenitemarray[] = $_item->getProductId();
                        $heading = "Freezer/Fridge";
                    }
                    endforeach;    
            } 
            foreach ($freshCategirylist as $_freshitem){
                foreach ($allItems as $_item): 
                    $product = $objectManager->create('Magento\Catalog\Model\Product')->load($_item->getProductId());
                    $categoryCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
                    $categoryIds = $product->getCategoryIds();
                    if(in_array($_freshitem,$categoryIds)){
                        $freshitemarray[] = $_item->getProductId();
                        $heading1 = "Fresh Vegetables";
                    }
                    endforeach;    
            }

            /* Add body */
            foreach ($allItems as $item) {
                if ($item->getParentItem()) {
                    continue;
                }

                /* Keep it compatible with the invoice */
                $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
                $categoryCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
                $categoryIds = $product->getCategoryIds();
                if(in_array($item->getProductId(),$otheritems)){
                    $item->setQty($item->getQtyOrdered());
                    $item->setOrderItem($item); 
                    $this->_drawItem($item, $page, $order);
                    $page = end($pdf->pages);

                }


                /* Draw item */
            }
            $page->drawLine(25, $this->y+10.5, 570, $this->y+10.5);
            $this->y -= 15;
            $top = $this->y;

            $this->_setFontBold($page, 12);
            $page->setFillColor(new \Zend_Pdf_Color_RGB(255,0,0));
            $page->drawText($heading, 35, $top -=-14, 'UTF-8');
            $page->setFillColor(new \Zend_Pdf_Color_RGB(0,0,0));
            foreach ($allItems as $item) {
                if ($item->getParentItem()) {
                    continue;
                }

                $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
                $categoryCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
                $categoryIds = $product->getCategoryIds();
                if(in_array($item->getProductId(),$frozenitemarray)){ 
                    $item->setQty($item->getQtyOrdered());
                    $item->setOrderItem($item); 
                    $this->_drawItem($item, $page, $order);
                    $page = end($pdf->pages);

                }
            }          
            $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawLine(25, $this->y+12.5, 570, $this->y+12.5);
            $this->y -= 15;
            $top = $this->y;
            $this->_setFontBold($page, 12);
            $page->setFillColor(new \Zend_Pdf_Color_RGB(255,0,0));
            $page->drawText($heading1, 34, $top -= -14, 'UTF-8');
            $page->setFillColor(new \Zend_Pdf_Color_RGB(0,0,0));

            foreach ($allItems as $item) {
                if ($item->getParentItem()) {
                    continue;
                }

                $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getProductId());
                $categoryCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
                $categoryIds = $product->getCategoryIds();
                if(in_array($item->getProductId(),$freshitemarray)){ 
                    $item->setQty($item->getQtyOrdered());
                    $item->setOrderItem($item); 
                    $this->_drawItem($item, $page, $order);
                    $page = end($pdf->pages);

                }
            }
            //$page = end($pdf->pages);

            // $this->_drawHeader($page);
            // /* Add body */
            // foreach ($order->getAllItems() as $item) {
            //     if ($item->getParentItem()) {
            //         continue;
            //     }

            //     /* Keep it compatible with the invoice */
            //     $item->setQty($item->getQtyOrdered());
            //     $item->setOrderItem($item);

            //     /* Draw item */
            //     $this->_drawItem($item, $page, $order);
            //     $page = end($pdf->pages);
            // }
            /* Add totals */
            $this->insertTotals($page, $order);
            if ($order->getStoreId()) {
                $this->appEmulation->stopEnvironmentEmulation();
                // $this->_localeResolver->revert();
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }
    protected function insertOrder(&$page, $obj, $putOrderId = true)
    {          
	
        if ($obj instanceof \Magento\Sales\Model\Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof \Magento\Sales\Model\Order\Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        $this->y = $this->y ? $this->y : 815;
        $top = 800;
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, $top, 570, $top - 55);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
        $this->_setFontRegular($page, 10);
         $this->insertLogo($page, $order->getStore(),225);

        if ($putOrderId) {
            $page->drawText(__('Order # ') . $order->getRealOrderId(), 400, $top -= 30, 'UTF-8');
            $top +=15;
        }
        $top -=30;
        $page->drawText(
            __('Order Date: ') .
            $this->_localeDate->formatDate(
                $this->_localeDate->scopeDate(
                    $order->getStore(),
                    $order->getCreatedAt(),
                    true
                ),
                \IntlDateFormatter::MEDIUM,
                false
            ),
            400,
            $top,
            'UTF-8'
        );
        $page->drawText(__('ABN: ') . $order->getAbn(), 400, $top -= 15, 'UTF-8');
        $top +=20;
        $page->drawText(__('')." ", 400, $top -= 30, 'UTF-8');

		$top -=30;
        /*   $page->drawText(__('')." ", 35, $top -= 30, 'UTF-8');
        $top +=15;
        $page->drawText(__('')." ", 35, $top -= 30, 'UTF-8');
        $top +=15;*/
        #$this->insertLogo($page, $order->getStore());

        $top -= 0;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 275, $top - 25);
        $page->drawRectangle(275, $top, 570, $top - 25);

        /* Calculate blocks info */

        /* Billing Address */
        $billingAddress = $this->_formatAddress($this->addressRenderer->format($order->getBillingAddress(), 'pdf'));

        /* Payment */
        $paymentInfo = $this->_paymentData->getInfoBlock($order->getPayment())->setIsSecureMode(true)->toPdf();
        $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress(
                $this->addressRenderer->format($order->getShippingAddress(), 'pdf')
            );
            $shippingMethod = $order->getShippingDescription();
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        $page->drawText(__('Sold to:'), 35, $top - 15, 'UTF-8');

        if (!$order->getIsVirtual()) {
            $page->drawText(__('Ship to:'), 285, $top - 15, 'UTF-8');
        } else {
            $page->drawText(__('Payment Method:'), 285, $top - 15, 'UTF-8');
        }

        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        foreach ($billingAddress as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }

        $addressesEndY = $this->y;

        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;
            foreach ($shippingAddress as $value) {
                if ($value !== '') {
                    $text = [];
                    foreach ($this->string->split($value, 45, true, true) as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }
            }

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 275, $this->y - 25);
            $page->drawRectangle(275, $this->y, 570, $this->y - 25);

            $this->y -= 15;
            $this->_setFontBold($page, 12);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $page->drawText(__('Payment Method:'), 35, $this->y, 'UTF-8');
            $page->drawText(__('Shipping Method:'), 285, $this->y, 'UTF-8');

            $this->y -= 10;
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($page, 10);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = $addressesStartY;
            $paymentLeft = 285;
        }
     
			
			$payment1 = $order->getPayment();
			$method = $payment1->getMethodInstance();
			$methodCode = $method->getCode();
			if($methodCode == 'banktransfer'){
				$additional = $payment1->getAdditionalInformation();
				$payment[] = $additional['instructions'];
			}

			/*foreach ($order->getAllItems() as $item) {
				$productId = $item->getProductId();
				$sellerId = $mpProCollection->create()
					->addFieldToFilter('mageproduct_id', $productId)
					->setPageSize(1)
					->getFirstItem()
					->getSellerId();
					
					#echo $sellerId;exit;
				if (!empty($sellerId)) {
					$bankDetails = $mpHelper->getSellerCollectionObj($sellerId)->setPageSize(1)
						->getFirstItem()
						->getBankDetails();
					if (!empty($bankDetails)) {
						$payment[] =$bankDetails;
					}
				}
				break;
			}*/
			#print_r($payment);exit;
        foreach ($payment as $value) {
            if (trim($value) != '') {
                //Printing "Payment Method" lines
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $page->drawText(strip_tags(trim($_value)), $paymentLeft, $yPayments, 'UTF-8');
                    $yPayments -= 15;
                }
            }
        }
         $yPayments -= 50;

        if ($order->getIsVirtual()) {
            // replacement of Shipments-Payments rectangle block
            $yPayments = min($addressesEndY, $yPayments);
            $page->drawLine(25, $top - 25, 25, $yPayments);
            $page->drawLine(570, $top - 25, 570, $yPayments);
            $page->drawLine(25, $yPayments, 570, $yPayments);

            $this->y = $yPayments - 15;
        } else {
            $topMargin = 15;
            $methodStartY = $this->y;
            $this->y -= 15;

            foreach ($this->string->split($shippingMethod, 45, true, true) as $_value) {
                $page->drawText(__('Total Shipping Charges')
                    . " "
                    . $order->formatPriceTxt($order->getShippingAmount())
                    , 285, $this->y, 'UTF-8');
                $this->y -= 5;
            }

            $yShipments = $this->y;
            /*     $totalShippingChargesText = "("
            . __('Total Shipping Charges')
            . " "
            . $order->formatPriceTxt($order->getShippingAmount())
            . ")";

            $page->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
            $yShipments -= $topMargin + 10;*/

            
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            if($order->getShippingArrivalDate()):
                $deliveryDate = $objectManager->get('Bss\OrderDeliveryDate\Helper\Data')->formatDate($order->getShippingArrivalDate());
                else:
                $deliveryDate=   __('N/A');
                endif;
            if($order->getShippingArrivalTimeslot()):
                $deliveryTimeSlot = $order->getShippingArrivalTimeslot();
                else:
                $deliveryTimeSlot=   __('N/A');
                endif;
            if($order->getShippingArrivalComments()):
                $deliveryComments = $order->getShippingArrivalComments();
                else:
                $deliveryComments=   __('No Comment');
                endif;

          
            $page->drawText(__('Delivery schedule : ').$deliveryDate." between".$deliveryTimeSlot, 285, $yShipments - 15, 'UTF-8');
            #$page->drawText(__('Shipping Arrival Timeslot :').$deliveryTimeSlot, 290, $yShipments - 25, 'UTF-8');
            $page->drawText(__('Customer Comments :').$deliveryComments, 285, $yShipments - 30, 'UTF-8');
            $this->y -= 5;
            
            
            $tracks = [];
            if ($shipment) {
                $tracks = $shipment->getAllTracks();
            }
            if (count($tracks)) {
                $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
                $page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $page->drawLine(400, $yShipments, 400, $yShipments - 10);
                //$page->drawLine(510, $yShipments, 510, $yShipments - 10);

                $this->_setFontRegular($page, 9);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                //$page->drawText(__('Carrier'), 290, $yShipments - 7 , 'UTF-8');
                $page->drawText(__('Title'), 290, $yShipments - 7, 'UTF-8');
                $page->drawText(__('Number'), 410, $yShipments - 7, 'UTF-8');

                $yShipments -= 20;
                $this->_setFontRegular($page, 8);
                foreach ($tracks as $track) {
                    $maxTitleLen = 45;
                    $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                    $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                    $page->drawText($truncatedTitle, 292, $yShipments, 'UTF-8');
                    $page->drawText($track->getNumber(), 410, $yShipments, 'UTF-8');
                    $yShipments -= $topMargin - 5;
                }
            } else {
                $yShipments -= $topMargin - 5;
            }

            $currentY = min($yPayments, $yShipments);

            // replacement of Shipments-Payments rectangle block
            $page->drawLine(25, $methodStartY, 25, $currentY);
            //left
            $page->drawLine(25, $currentY, 570, $currentY);
            //bottom
            $page->drawLine(570, $currentY, 570, $methodStartY);
            //right

            $this->y = $currentY;
            $this->y -= 15;
        }
    }
}