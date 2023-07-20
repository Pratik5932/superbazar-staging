<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ZipCodeValidator
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\ZipCodeValidator\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class BeforeViewProduct implements ObserverInterface
{
    /**
     * @param RequestInterface $request
     * @param \Webkul\ZipCodeValidator\Helper\Data $helper
     * @param \Magento\Catalog\Model\ProductFactory $product
     */
    public function __construct(
        RequestInterface $request,
        \Webkul\ZipCodeValidator\Helper\Data $helper,
        \Magento\Catalog\Model\ProductFactory $product
    ) {
        $this->request = $request;
        $this->helper = $helper;
        $this->product = $product;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $data = $this->request->getParams();
            $productId = $data['id'];
            $applyStatus = $this->helper->getApplyStatus();
            if ($applyStatus) {
                $availableregions = $this->helper->getConfigRegion();
                if ($availableregions) {
                    $regionIds = explode(',', $availableregions);
                    if (!empty($regionIds)) {
                        $data = [
                            'available_region' => $regionIds
                        ];
                        /* $product = $this->product->create()->load($productId);
                        $product->addData($data)->setId($productId)->save(); */
                    }
                }
            }
        } catch (\Exception $e) {
        }
    }
}
