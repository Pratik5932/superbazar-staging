<?php

namespace Superbazaar\General\Block;

class StockNotify extends \Magento\ProductAlert\Block\Product\View {


    protected function getProduct()
    {
        $product = $this->getProuctinfo();
        if ($product!== null && $product->getId()) {
            return $product;
        }
        return false;
    }
    public function setTemplate($template)
    {
        if (!$this->_helper->isStockAlertAllowed() || !$this->getProduct() || $this->getProduct()->isAvailable()) {
            $template = '';
        } else {
            $this->setSignupUrl($this->getSaveUrl('stock'));
        }
        return parent::setTemplate($template);
    }
     /**
     * @param string $type
     * @return string
     */
    public function getSaveUrl($type)
    {

        return $this->getUrl(
            'productalert/add/' . $type,
            [
                'product_id' => $this->getProduct()->getId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>$this->_helper->getEncodedUrl()
            ]
        );
    }     
}