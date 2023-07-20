<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulMp
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\MobikulMp\Plugin\Model\Product;

use Closure;
use Magento\Framework\App\RequestInterface;

class TypeTransitionManager
{
    /**
     * Request instance
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Change product type to configurable if needed
     *
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $subject subject
     * @param Closure                                              $proceed proceed
     * @param \Magento\Catalog\Model\Product                       $product product
     *
     * @return void
     */
    public function aroundProcessProduct(
        \Magento\Catalog\Model\Product\TypeTransitionManager $subject,
        Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        $authKey = $this->request->getHeader('authKey');
        $productType = $product->getTypeId();
        if ($productType != "downloadable" && empty($authKey)) {
            $proceed($product);
        }
    }
}
