<?php
namespace Poli\PoliPayments\Model;

use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;

class PoliPayments extends \Magento\Payment\Model\Method\AbstractMethod {

    protected $_code = 'polipayments';
    protected $_exception;
    protected $_transactionRepository;
    protected $_transactionBuilder;
    protected $_urlBuilder;
    protected $_order;
    protected $_orderSender;
    protected $_orderFactory;
    protected $_storeManager;
    protected $_invoiceSender;

    public function __construct(
		\Magento\Framework\UrlInterface $urlBuilder,
		\Magento\Framework\Exception\LocalizedExceptionFactory $exception,
		\Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
		\Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
		\Magento\Sales\Model\OrderFactory $orderFactory,
		\Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
		\Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Model\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
		\Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
		\Magento\Payment\Helper\Data $paymentData,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Payment\Model\Method\Logger $logger,
		\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
		\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
		array $data = []
    ) {
		$this->_urlBuilder = $urlBuilder;
		$this->_exception = $exception;
		$this->_transactionRepository = $transactionRepository;
		$this->_transactionBuilder = $transactionBuilder;
		$this->_orderSender = $orderSender;
		$this->_orderFactory = $orderFactory;
		$this->_storeManager = $storeManager;
		$this->_invoiceSender = $invoiceSender;

      parent::__construct(
          $context,
          $registry,
          $extensionFactory,
          $customAttributeFactory,
          $paymentData,
          $scopeConfig,
          $logger,
          $resource,
          $resourceCollection,
          $data
      );
    }
	
	public function update_order( $reference, $currency, $gateway_status, $pay_reference, $amount ){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_order = $this->_orderFactory->create()->loadByIncrementId( $reference );
        if (!$this->_order && $this->_order->getId()) {
            throw new Exception('Could not find Magento order with id $reference');
        }

		// Payment
		$payment = $this->_order->getPayment();
        $payment->setTransactionId($pay_reference);
        $payment->setCurrencyCode($currency);
        $payment->setPreparedMessage($gateway_status);
        $payment->setParentTransactionId($pay_reference);
        $payment->setShouldCloseParentTransaction(true);
        $payment->setIsTransactionClosed(0);
        $payment->registerCaptureNotification($amount, true );
        $this->_order->save();		
		
		if($this->_order->canInvoice()) {
			$invoice = $objectManager->create('Magento\Sales\Model\Service\InvoiceService')->prepareInvoice($this->_order);
			$invoice->register();
			$invoice->pay();
			$invoice->save();
			$transactionSave = $objectManager->create('Magento\Framework\DB\Transaction'
				)->addObject( $invoice
				)->addObject( $invoice->getOrder()
			);
			$transactionSave->save();
			$this->_order->addStatusHistoryComment(__('Created invoice #%1.', $invoice->getId()))
				->setIsCustomerNotified(false)
				->save();

			$invoiceSender = $objectManager->get('Magento\Sales\Model\Order\Email\Sender\InvoiceSender');
            $invoiceSender->send($invoice);
			$this->_order->addStatusHistoryComment(__('Notified customer about invoice #%1.', $invoice->getId()))
				->setIsCustomerNotified(true)
				->save();
		}
		
		// Paid
		$this->_order->setStatus( 'processing' );
		$this->_order->save();		
	}

	public function process_start( $order ){
		$poli_debug=false;
		// Get customer		
		$customer_id=$order->GetCustomerId();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$customer = $objectManager->create('Magento\Customer\Model\Customer')->load($customer_id);
		$name=$customer->getName();

		// Set status
		$order->setStatus( 'pending_payment' )->save();
		
		// Get order
		$reference=$order->getIncrementId();
		$currency=$order->GetStoreCurrencyCode();
		$grandtotal=number_format( $order->getGrandTotal(), 2, ".", "" );
		$payreference="";
		$url="";
		$errortxt="";
		list( $payreference, $url, $errortxt )=$this->initiate( $reference, $name, $currency, $grandtotal, "", $poli_debug );
		return $url;
	}

	public function process_nudge( ){
		$poli_debug=false;
		list($success, $pay_reference, $currency, $amount, $gateway_status, $reference, $merchant_data, $bank )=$this->nudge( $poli_debug );
		return array($success, $pay_reference, $currency, $amount, $gateway_status, $reference, $merchant_data, $bank );
	}
    public function _getUrl($path = "", $secure = null)
    {
        $store = $this->_storeManager->getStore(null);

        return $this->_urlBuilder->getUrl(
            $path,
            ['_store' => $store, '_secure' => $secure === null ? $store->isCurrentlySecure() : $secure]
        );
    }
	private function escape_field( $text ){
		$clean_code = substr( preg_replace('/[^a-zA-Z0-9\.@ ]/', '', $text), 0, 12 );
		return $clean_code;
	}
	private function escape_html( $text ){
		return urlencode( $text );
	}
	private function make_tracking_code( ){
		$len=6;
		$str="";
		for($i=0;$i<$len;$i++){
			$str.=chr((rand()%26)+97);
		}
		return strtoupper( $str ) . date( "GsHi" );
	}
  	private function stripstring($start, $end, $total){
		$total = stristr($total, $start);
		$f2 = stristr($total, $end);
		$result=urldecode( substr($total,strlen($start),-strlen($f2)) );
		return $result;
	} 
	private function poli_encrypt($data_input, $key='g84hv8trhe84d'){     
		$td = mcrypt_module_open('cast-256', '', 'ecb', '');
		$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td, $key, $iv);
		$encrypted_data = mcrypt_generic($td, $data_input);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		$encoded_64=base64_encode($encrypted_data);
		return $encoded_64;
	}   
	private function poli_decrypt($encoded_64, $key='g84hv8trhe84d'){
		$decoded_64=base64_decode($encoded_64);
		$td = mcrypt_module_open('cast-256', '', 'ecb', '');
		$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td, $key, $iv);
		$decrypted_data = mdecrypt_generic($td, $decoded_64);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
	
		$last_char=substr($decrypted_data,-1);
		$p=strpos( $decrypted_data, $last_char );
		$decrypted_data=substr($decrypted_data,0,$p);
		return $decrypted_data;
	}

	private function nudge( $poli_debug=false ){
		$token="";
		if(isset($_POST['Token'])){
			$token=$_POST['Token'];
		} else if(isset($_REQUEST['Token'])){
			$token=$_REQUEST['Token'];
		}
		if( $token == "" ){
			$debug_text="No token";
			if( $poli_debug == true  ){
				echo nl2br( $debug_text );
				mail( "apptest@merco.co.nz", "Nudge", $debug_text );
			}
			die();
		}
		
		$auth = base64_encode( $this->getConfigData('merchantcode').":".$this->getConfigData('authcode') );
		$header = array();
		$header[] = 'Authorization: Basic '.$auth;
		$ch = curl_init("https://poliapi.apac.paywithpoli.com/api/Transaction/GetTransaction?token=".urlencode($token));
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt( $ch, CURLOPT_HEADER, 0);
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt( $ch, CURLOPT_POST, 0);
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0);
		$referrer = "";
		curl_setopt($ch, CURLOPT_REFERER, $referrer);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec( $ch );
		curl_close ($ch);
		$response_json = json_decode( $response, true );

		$gateway_status=$response_json['TransactionStatusCode'];
		$pay_reference=$response_json['TransactionRefNo'];
		$bank=$response_json['FinancialInstitutionCode'];
		$ref=explode( "|", $response_json['MerchantReference'] );
		$reference=$ref[0];
		$currency=$response_json['CurrencyCode'];
		$amount=$response_json['AmountPaid'];
		$merchant_data_raw=str_replace( " |", "|", $response_json['MerchantReferenceData'] );
		$merchant_data=explode( "|", $merchant_data_raw );
		if( $gateway_status == "Completed" ){
			$success = true;
		} else {
			$success = false;
		}

		if( $poli_debug == true  ){
			$debug_text="response_json=".print_r( $response_json, true ).
"success=".$success."
gateway_status=".$gateway_status."
reference=".$reference."
pay_reference=".$pay_reference."
";
			echo nl2br( $debug_text );
			mail( "apptest@merco.co.nz", "Nudge", $debug_text );		
		}

		return array( $success, $pay_reference, $currency, $amount, $gateway_status, $reference, $merchant_data, $bank );
	}

	private function initiate( $reference, $name, $currency, $grandtotal, $extra_merchant_data="", $poli_debug=false ){
		$api_url = 'https://merchantapi.apac.paywithpoli.com/MerchantAPIService.svc/Xml/transaction/initiate';
		$today = date("Y-m-d");
		$time = date("h:i:s");
		$date= $today."T".$time;

		// Make fields
		if( $reference == "" ) $reference=$this->make_tracking_code();
		$reference_short=$this->escape_field( $reference );
		$name_short=$this->escape_field( $name );
		$merchant_ref=$reference_short."|".$name_short."||";
		$merchant_ref=substr( $merchant_ref, 0, 100 );
		$merchant_data=$reference."|".$extra_merchant_data;
		$merchant_data=substr( $merchant_data, 0, 2000 );
		$merchant_data=str_replace( "\n", " ", $merchant_data );
		$merchant_data=str_replace( "\r", " ", $merchant_data );
		$merchant_data=str_replace( "|", " |", $merchant_data );
		// $merchant_data=$this->escape_html( $merchant_data );

		// Links
		$homepage=$this->_getUrl( );
		$nudge=$this->_getUrl( "polipayments/checkout/nudge" );
		$success=$this->_getUrl( "checkout/onepage/success" );
		$failure=$this->_getUrl( "polipayments/checkout/failure" );
		$cancel=$this->_getUrl( "polipayments/checkout/failure" );
		
		// Initiate
		$auth = base64_encode( $this->getConfigData('merchantcode').":".$this->getConfigData('authcode') );
		$header = array();
		$header[] = 'Content-Type: application/json';
		$header[] = 'Authorization: Basic '.$auth;
		$transaction_json = '{
			"Amount":"'.$grandtotal.'",
			"CurrencyCode":"'.$currency.'",
			"MerchantReference":"'.$merchant_ref.'",
			"MerchantData":"'.$merchant_data.'",
			"MerchantReferenceFormat":"4",
			"MerchantHomepageURL":"'.$homepage.'",
			"SuccessURL":"'.$success.'",
			"FailureURL":"'.$failure.'",
			"CancellationURL":"'.$cancel.'",
			"NotificationURL":"'.$nudge.'"
		}';
		$ch = curl_init("https://poliapi.apac.paywithpoli.com/api/Transaction/Initiate");
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt( $ch, CURLOPT_HEADER, 0);
		curl_setopt( $ch, CURLOPT_POST, 1);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $transaction_json );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec( $ch );
		curl_close ($ch);
		$response_json = json_decode($response, true);
		$url="";
		$errortxt="";
		if( isset( $response_json['NavigateURL'] ) and $response_json['NavigateURL'] != "" ) $url=$response_json['NavigateURL'];
		if( isset( $response_json['ErrorMessage'] ) and $response_json['ErrorMessage'] != "" ) $errortxt=$response_json['ErrorMessage'];
		if( isset( $response_json['Message'] ) and $response_json['Message'] != "" ) $errortxt=$response_json['Message'];
		if( isset( $response_json['TransactionRefNo'] ) and $response_json['TransactionRefNo'] != "" ) $transactionToken=$response_json['TransactionRefNo'];

		if( $poli_debug == true  ){
			$debug_text="transaction_json=".$transaction_json;
			echo nl2br( $debug_text );
			echo "<BR><a href=\"".$url."\" target=_blank>".$url."</a>";
			mail( "apptest@merco.co.nz", "Nudge", $debug_text );		
		}

		return array( $reference, $url, $errortxt );
	}
}
