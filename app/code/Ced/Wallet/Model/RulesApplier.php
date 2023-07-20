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

use Magento\Quote\Model\Quote\Address;


class RulesApplier extends \Magento\SalesRule\Model\RulesApplier
{
    
    public function applyRules($item, $rules, $skipValidation, $couponCode)
    {
    	
	       	$address = $item->getAddress();
	        $appliedRuleIds = [];
	        /* @var $rule \Magento\SalesRule\Model\Rule */
	        foreach ($rules as $rule) {
	        	if($item->getSku() == "wallet_product")
	        	{
	        		continue;
	        	}
	            if (!$this->validatorUtility->canProcessRule($rule, $address)) {
	                continue;
	            }
	
	            if (!$skipValidation && !$rule->getActions()->validate($item)) {
	                $childItems = $item->getChildren();
	                $isContinue = true;
	                if (!empty($childItems)) {
	                    foreach ($childItems as $childItem) {
	                        if ($rule->getActions()->validate($childItem)) {
	                            $isContinue = false;
	                        }
	                    }
	                }
	                if ($isContinue) {
	                    continue;
	                }
	            }
	
	            $this->applyRule($item, $rule, $address, $couponCode);
	            $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();
	
	            if ($rule->getStopRulesProcessing()) {
	                break;
	            }
	        }
	
	        return $appliedRuleIds;
	      
    }
}
