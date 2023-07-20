<?php
namespace Magecomp\Paymentfee\Model;
use Magento\Framework\Exception\AuthenticationException;

class Addpaymentfee implements \Magecomp\Paymentfee\Api\PaymentfeeInterface
{
    protected $quoteFactory;
    protected $_helperdata;
    protected $_emulation;
    protected $totalsCollector;
   
   public function __construct(
    \Magento\Quote\Api\CartRepositoryInterface $quoteFactory,
    \Magecomp\Paymentfee\Helper\Data $helperData,
    \Magento\Store\Model\App\Emulation $emulation,
    \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
    ) {
        
        $this->quoteFactory = $quoteFactory;
        $this->_helperdata = $helperData;
        $this->_emulation = $emulation;
        $this->totalsCollector = $totalsCollector;
    } 

    public function addPaymentFee($quoteid,$storeid)
    {
        try {

            if (empty($quoteid) || empty($storeid) ) {
                $response = ["status"=>false, "message"=>__("Invalid parameter list.")];
            }
            $quote = $this->quoteFactory->get($quoteid);
            $this->_emulation->startEnvironmentEmulation($storeid , 'frontend');
            $this->totalsCollector->collectQuoteTotals($quote);
            $quote->save();
            $this->_emulation->stopEnvironmentEmulation();
            $response = [
                        "status"=>true, 
                        "fee_title"=> $quote->getShippingAddress()->getMcPaymentfeeDescription(),
                        "fee_amount"=> $quote->getShippingAddress()->getMcPaymentfeeAmount()
            ];        
            return json_encode($response);

        } catch (\Exception $e) {
            throw new AuthenticationException(__($e->getMessage()));
        }
        
    }   
    
}
