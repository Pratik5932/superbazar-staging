<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulMpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MobikulMpHyperLocal\Controller;

use Magento\Store\Model\App\Emulation;
use Webkul\Marketplace\Model\SellerFactory;
use Webkul\MpHyperLocal\Model\ShipAreaFactory;
use Webkul\MpHyperLocal\Model\ShipRateFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;

abstract class AbstractApi extends \Webkul\MobikulApi\Controller\ApiController  {

    
    protected $_emulate;
    protected $_shipArea;
    protected $_shipRate;
    protected $_mpHelper;
    protected $_addRateBlock;
    protected $sellerFactory;
    protected $_customerSession;
    protected $_fileUploaderFactory;

    public function __construct(
        Emulation $emulate,
        ShipAreaFactory $shipArea,
        ShipRateFactory $shipRate,
        SellerFactory $sellerFactory,
        UploaderFactory $fileUploaderFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MobikulCore\Helper\Data $mobikulHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpHyperLocal\Block\Account\AddRate $addRateBlock
    )  {
        $this->_emulate         = $emulate;
        $this->_shipRate        = $shipRate;
        $this->_mpHelper        = $mpHelper;
        $this->_shipArea        = $shipArea;
        $this->_addRateBlock    = $addRateBlock;
        $this->sellerFactory    = $sellerFactory;
        $this->_customerSession = $customerSession;
        $this->_helper = $mobikulHelper;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        parent::__construct($mobikulHelper, $context,$jsonHelper);
    }
}
