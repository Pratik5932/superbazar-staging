<?php
namespace Webkul\Customisation\Model\Api;

class StripeSellerTransactionApiManagement implements \Webkul\Customisation\Api\StripeSellerTransactionApiManagementInterface
{
    const SEVERE_ERROR = 0;
    const SUCCESS = 1;
    const LOCAL_ERROR = 2;

    /**
     * @var \Webkul\MpStripe\Model\ResourceModel\StripeSeller\CollectionFactory
     */
    protected $stripeSellerFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Webkul\MpStripe\Helper\Data $helper
     */
    protected $helper;

    /**
     * @param \Webkul\MpStripe\Model\ResourceModel\StripeSeller\CollectionFactory $stripeSellerFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper,
     * @param \Webkul\MpStripe\Helper\Data $helper
     */
    public function __construct(
        \Webkul\MpStripe\Model\ResourceModel\StripeSeller\CollectionFactory $stripeSellerFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Webkul\MpStripe\Helper\Data $helper

    ) {
        $this->stripeSellerFactory = $stripeSellerFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->pricingHelper = $pricingHelper;
        $this->helper = $helper;
    }

    /**
     * Get stripe seller transaction Api data.
     *
     * @api
     *
     * @param int $id
     * @return array
     */
    public function getApiData($id)
    {
        try {
            $sellerId = $id;
            $sellerData = $this->stripeSellerFactory->create()
            ->addFieldToFilter('seller_id', ['eq' => $sellerId])->getFirstItem();
            $this->helper->setUpDefaultDetails();
            $response = \Stripe\Transfer::all(["destination" => $sellerData->getStripeUserId()]);
            $transactions = $response['data'];
            $data = [];
            if ($transactions) {
                foreach ($transactions as $transfer) {
                    $eachData = [];
                    $eachData = [
                        'date_paid' => date('d/M/Y h:i:s', $transfer['created']),
                        'amount' => $this->getPriceHtml($transfer['amount']),
                        'id' => $transfer['id'],
                        'transaction_id' => $transfer['balance_transaction'],
                        'transfer_group' => $transfer['transfer_group']
                    ];
                    $data[] = $eachData;
                }
            } else {
                $data[] = [
                    'message' => __("No Transactions available")
                ];
            }
            return $data;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = self::SEVERE_ERROR;
            return $returnArray;
        }
    }

    /**
     * Get price html
     * 
     * @param int
     * @return int
     */
    public function getPriceHtml($price)
    {
        return $this->pricingHelper->currency(number_format($price, 2), true, false);
    }
}
