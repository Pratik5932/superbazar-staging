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
namespace Webkul\Mppaypalexpresscheckout\Block\Account;

use Webkul\Mppaypalexpresscheckout\Model\ResourceModel\Mppaypalexpresscheckout\CollectionFactory;

/**
 * Mppaypalexpresscheckout PaypalInfo block.
 */
class PaypalInfo extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CollectionFactory
     */
    private $sellerCollectionFactory;

    /**
     * @var \Webkul\Mppaypalexpresscheckout\Helper\Data
     */
    private $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CollectionFactory                                $sellerCollectionFactory
     * @param \Webkul\Mppaypalexpresscheckout\Helper\Data      $helper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CollectionFactory $sellerCollectionFactory,
        \Webkul\Mppaypalexpresscheckout\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sellerCollectionFactory = $sellerCollectionFactory;
        $this->helper = $helper;
    }

    public function getSellerPaypalData()
    {
        try {
            if (!($customerId = $this->helper->getSellerId())) {
                return false;
            }
            $sellercollection = $this->sellerCollectionFactory->create()
                ->addFieldToFilter(
                    'seller_id',
                    $customerId
                );

            return $sellercollection;
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Block_Account_PaypalInfo getSellerPaypalData : ".$e->getMessage());
        }
    }
}
