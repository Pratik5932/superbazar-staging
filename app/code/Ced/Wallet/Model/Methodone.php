<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Wallet
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Wallet\Model;



/**
 * Pay In Store payment method model
 */
class Methodone extends \Magento\Payment\Model\Method\AbstractMethod
{


    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;

    const METHOD_CODE               = 'wallet';
    
    /**
     * @var string
     */
    protected $_code                    = self::METHOD_CODE;
}
