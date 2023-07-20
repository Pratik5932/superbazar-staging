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

/**
 * Config model that is aware of all \Webkul\Mppaypalexpresscheckout payment methods
 */
class Config extends AbstractConfig
{
    /**
     * mppaypalexpresscheckout - alias METHOD_CODE.
     */
    const METHOD_CODE = 'mppaypalexpresscheckout';

    /**#@+
     * Refund types
     */
    const REFUND_TYPE_FULL = 'Full';

    const REFUND_TYPE_PARTIAL = 'Partial';

    /**#@-*

    /**
     * Core data
     *
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Directory\Helper\Data                     $directoryHelper
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param array                                              $params
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $params = []
    ) {
        parent::__construct($scopeConfig);
        $this->directoryHelper = $directoryHelper;
        $this->_storeManager = $storeManager;
        if ($params) {
            $method = array_shift($params);
            $this->setMethod($method);
            if ($params) {
                $storeId = array_shift($params);
                $this->setStoreId($storeId);
            }
        } else {
            $this->setMethod('mppaypalexpresscheckout');
        }
    }

    /**
     * Check whether method available for checkout or not
     * Logic based on merchant country, methods dependence.
     *
     * @param string|null $methodCode
     *
     * @return                                       bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isMethodAvailable($methodCode = null)
    {
        $result = parent::isMethodAvailable($methodCode);

        switch ($methodCode) {
            case self::METHOD_CODE:
            case self::METHOD_CODE:
                if ($this->isMethodActive(self::METHOD_CODE)
                || $this->isMethodActive(self::METHOD_CODE)
                    ) {
                        $result = true;
                }
                break;
        }

        return $result;
    }

    /**
     * PayPal web URL generic getter.
     *
     * @param array $params
     *
     * @return string
     */
    public function getPaypalUrl(array $params = [])
    {
        return sprintf(
            'https://www.%spaypal.com/cgi-bin/webscr%s',
            $this->getValue('sandboxFlag') ? 'sandbox.' : '',
            $params ? '?'.http_build_query($params) : ''
        );
    }

    /**
     * Payment actions source getter.
     *
     * @return array
     */
    public function getPaymentActions()
    {
        $paymentActions = [
            self::PAYMENT_ACTION_AUTH => __('Authorization'),
            self::PAYMENT_ACTION_SALE => __('Sale'),
        ];
        if ($this->_methodCode !== null && $this->_methodCode == self::METHOD_CODE) {
            $paymentActions[self::PAYMENT_ACTION_ORDER] = __('Order');
        }

        return $paymentActions;
    }

    /**
     * Mapper from PayPal-specific payment actions to Magento payment actions.
     *
     * @return string|null
     */
    public function getPaymentAction()
    {
        switch ($this->getValue('paymentAction')) {
            case self::PAYMENT_ACTION_AUTH:
                return \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE;
            case self::PAYMENT_ACTION_SALE:
                return \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE;
            case self::PAYMENT_ACTION_ORDER:
                return \Magento\Payment\Model\Method\AbstractMethod::ACTION_ORDER;
        }

        return;
    }

    /**
     * Map General Settings.
     *
     * @param string $fieldName
     *
     * @return string|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _mapMethodFieldset($fieldName)
    {
        if (!$this->_methodCode) {
            return;
        }
        switch ($fieldName) {
            case 'active':
            case 'title':
            case 'payment_action':
            case 'allowspecific':
            case 'specificcountry':
            case 'sort_order':
            case 'sandbox':
                return "payment/{$this->_methodCode}/{$fieldName}";
            default:
                return;
        }
    }
}
