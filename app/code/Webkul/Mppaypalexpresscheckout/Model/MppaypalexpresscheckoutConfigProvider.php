<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mppaypalexpresscheckout
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mppaypalexpresscheckout\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;

class MppaypalexpresscheckoutConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $_methodCodes = [
        Config::METHOD_CODE
    ];

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $_methods = [];

    /**
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
        PaymentHelper $paymentHelper
    ) {
        foreach ($this->_methodCodes as $code) {
            $this->_methods[$code] = $paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [
            'payment' => [
                'mppaypalexpresscheckoutData' => []
            ]
        ];
        foreach ($this->_methodCodes as $code) {
            if ($this->_methods[$code]->isAvailable()) {
                $config['payment']['mppaypalexpresscheckoutData']['redirectUrl'][$code] =
                $this->getMethodRedirectUrl($code);
            }
        }
        return $config;
    }

    /**
     * Return redirect URL for method
     *
     * @param  string $code
     * @return mixed
     */
    protected function getMethodRedirectUrl($code)
    {
        return $this->_methods[$code]->getCheckoutRedirectUrl();
    }
}
