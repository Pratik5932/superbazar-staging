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
namespace Webkul\MobikulMp\Plugin\Helper;

use Closure;
use Magento\Framework\App\RequestInterface;

class Data
{
    /**
     * Customer Session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     *
     * @param RequestInterface $request
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    public function aroundGetCustomerId(
        \Webkul\Marketplace\Helper\Data $subject,
        Closure $proceed
    ) {
        if ($this->customerSession->getCustomerId())
        {
            return $this->customerSession->getCustomerId();
        } else {
            return $proceed();
        }
    }
}
