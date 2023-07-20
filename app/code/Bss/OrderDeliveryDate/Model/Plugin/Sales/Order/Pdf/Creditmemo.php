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
// @codingStandardsIgnoreFile
namespace Bss\OrderDeliveryDate\Model\Plugin\Sales\Order\Pdf;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Creditmemo extends \Magento\Sales\Model\Order\Pdf\Creditmemo
{

    /**
     * @return \Bss\OrderDeliveryDate\Helper\Data|null|$this
     */
    public function getHelperObject()
    {
        return $this;
    }

    /**
     * @param array $creditmemos
     * @return \Zend_Pdf
     * @throws \Zend_Pdf_Exception
     */
    public function getPdf($creditmemos = [])
    {
        $helper = $this->getHelperObject();

        $this->_beforeGetPdf();
        $this->_initRenderer('creditmemo');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($creditmemos as $creditmemo) {
            if ($creditmemo->getStoreId()) {
                if ($helper->isLowerThan241Version()) {
                    $this->_localeResolver->emulate($creditmemo->getStoreId());
                } else {
                    $helper->getEmulationContext()->startEnvironmentEmulation(
                        $creditmemo->getStoreId(),
                        \Magento\Framework\App\Area::AREA_FRONTEND,
                        true
                    );
                }
                $this->_storeManager->setCurrentStore($creditmemo->getStoreId());
            }
            $page = $this->newPage();
            $order = $creditmemo->getOrder();
            /* Add image */
            $this->insertLogo($page, $creditmemo->getStore());
            /* Add address */
            $this->insertAddress($page, $creditmemo->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_CREDITMEMO_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Credit Memo # ') . $creditmemo->getIncrementId());
            /* Add delivery date*/
            $this->insertBssDeliveryDate($page, $order);
            /* Add table head */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($creditmemo->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
            /* Add totals */
            $this->insertTotals($page, $creditmemo);
        }
        $this->_afterGetPdf();
        if ($creditmemo->getStoreId()) {
            if ($helper->isLowerThan241Version()) {
                $this->_localeResolver->revert();
            } else {
                $helper->getEmulationContext()->stopEnvironmentEmulation();
            }
        }
        return $pdf;
    }

    /**
     * @param mixed $page
     * @param mixed $order
     */
    public function insertBssDeliveryDate(&$page, $order)
    {
        $helper = $this->getHelperObject();
        $shippingArrivalDate = $order->getShippingArrivalDate();
        if (!$shippingArrivalDate) {
            $shippingArrivalDate = __('N/A');
        } else {
            $shippingArrivalDate = $helper->formatDate($shippingArrivalDate);
        }
        $shippingArrivalTimeslot = $order->getShippingArrivalTimeslot();
        if (!$shippingArrivalTimeslot) {
            $shippingArrivalTimeslot = __('N/A');
        }
        $shippingArrivalComments = $order->getShippingArrivalComments();
        if (!$shippingArrivalComments) {
            $shippingArrivalComments = __('No Comment');
        } else {
            $shippingArrivalComments = $this->string->split($shippingArrivalComments, 100, true, true);
        }
        $positionLine = 0;
        if (is_array($shippingArrivalComments)) {
            foreach ($shippingArrivalComments as $shippingArrivalComment) {
                $positionLine += 17;
            }
        }
        $this->y -= -8;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 20);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(1, 1, 1));
        $page->drawRectangle(25, $this->y - 20, 570, $this->y - 77 - $positionLine);

        $page->setFillColor(new \Zend_Pdf_Color_RGB(0.1, 0.1, 0.1));
        $this->_setFontBold($page, 12);

        $currentDate = __('Shipping Arrival Date: ') . $shippingArrivalDate;
        $deliveryTimeSlot = __('Shipping Arrival Timeslot: ') . $shippingArrivalTimeslot;
        $page->drawText(__('Delivery Date Information'), 35, $this->y - 13, 'UTF-8');
        $this->_setFontRegular($page, 10);
        $page->drawText($currentDate, 33, $this->y - 33, 'UTF-8');
        $page->drawText($deliveryTimeSlot, 33, $this->y - 50, 'UTF-8');
        $position = 0;
        if (is_array($shippingArrivalComments)) {
            $comment = "";
            foreach ($shippingArrivalComments as $shippingArrivalComment) {
                if ($comment == "") {
                    $comment = __('Shipping Arrival Comment: ') . $shippingArrivalComment;
                } else {
                    $comment = $shippingArrivalComment;
                }
                $page->drawText($comment, 33, $this->y - 67 - $position, 'UTF-8');
                $position += 17;
            }
        } else {
            $comment = __('Shipping Arrival Comment: ') . $shippingArrivalComments;
            $page->drawText($comment, 33, $this->y - 67 - $position, 'UTF-8');
        }
        $this->y -= 84 + $position;
    }
}
