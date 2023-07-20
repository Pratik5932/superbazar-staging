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
namespace Ced\Wallet\Helper;

use Magento\Framework\Math\Random;

class GenerateOTP extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var Random
     */
    protected $_random;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Random $_random
    )
    {
        $this->_random = $_random;
        $this->_objectManager = $objectManager;
        parent::__construct($context);
    }

    public function otpEnabled()
    {
        return $this->scopeConfig->getValue('ced_wallet/active/otp_required_for_transaction',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

    }

    public function generateOTP($storeId = 0)
    {
        $format = $this->scopeConfig->getValue(
            'ced_wallet/active/otp_template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($format !== '' && $format != NULL){
            $replacement = [];
            $storeId = 0;
            $replaceRandValue = $this->replaceRandValue($format);

            $replaceStoreValue = $this->replaceOtherValue($replacement, $storeId);
            $replaceResult = array_merge($replaceRandValue, $replaceStoreValue);
            $otp = str_replace(
                        array_keys($replaceResult),
                        array_values($replaceResult),
                        $format
                    );

        }else{
            $otp = $this->_random->getRandomNumber(11111, 99999);
        }
        return $otp;

    }

    /**
     *
     * @param $replacement as an array
     * @return array for random numbers used in format
     */
    protected function replaceRandValue($format)
    {
        $random = $this->getRandomFormat('rand',$format,'setRandomNumber');
        $alphanumeric = $this->getRandomFormat('alphanum',$format,'setAlphaNumericRand');

        return array_merge($random,$alphanumeric);
    }

    protected function getRandomFormat($find, $format, $callback)
    {
        $start_index = 0;
        $randomArray = [];
        do {

            $randomFlag = false;
            $rand_index = strpos($format,$find,$start_index);
            if($rand_index !== false) {

                $randomFlag = true;
                $start = strpos($format," ",$rand_index)+1;

                $length = strpos($format,"}}",$rand_index)-$start;
                $rand_length = substr($format, $start, $length);
                $randomArray['{{'.$find.' '.$rand_length.'}}'] = $this->$callback($rand_length);

                $start_index = $start;
            }

        } while ($randomFlag);
        return $randomArray;
    }

    public function setRandomNumber($length)
    {
        return rand(pow(10, $length-1), pow(10, $length)-1);
    }


    public function setAlphaNumericRand($alpanum)
    {
        $string = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $storeRandom = '';
        $userDefineLen = $alpanum;
        $length = strlen($string);
        for($i = 0;$i < $userDefineLen;$i++)
        {
            $generateRandomValue = rand(0,$length-1);
            $storeRandom.= $string[$generateRandomValue];
        }
        return $storeRandom;
    }
    /**
     *
     * @param $replacement array
     * @return array
     */

    protected function replaceOtherValue($replacement, $storeId = 0)
    {
        $replacement["{{id}}"] = $storeId;

        return $replacement;
    }

}