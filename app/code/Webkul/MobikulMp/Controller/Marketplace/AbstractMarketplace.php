<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulMp
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\MobikulMp\Controller\Marketplace;

use Magento\Framework\Registry;
use Magento\Framework\Filesystem;
use Magento\Store\Model\App\Emulation;
use Magento\Sales\Model\OrderRepository;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as SellerProduct;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as MageProductCollection;
use Webkul\Marketplace\Model\ResourceModel\Sellertransaction\CollectionFactory as TransactionCollectionFactory;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
// use Webkul\Marketplace\Model\ProductFlags;
// use Webkul\Marketplace\Model\ProductFlagReason;
// use Webkul\Marketplace\Model\SellerFlags;
// use Webkul\Marketplace\Model\SellerFlagReason;

/**
 * Abstract Class AbstractMarketplace
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
abstract class AbstractMarketplace extends \Webkul\MobikulApi\Controller\ApiController
{
    /**
     * $date
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    
    /**
     * $order
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $order;
    
    /**
     * $track
     *
     * @var \Magento\Sales\Model\Order\Shipment\Track
     */
    protected $track;
    
    /**
     * $helper
     *
     * @var \Webkul\MobikulCore\Helper\Data
     */
    protected $helper;
    
    /**
     * $seller
     *
     * @var \Webkul\Marketplace\Model\Seller
     */
    protected $seller;
    
    /**
     * $emulate
     *
     * @var Magento\Store\Model\App\Emulation
     */
    protected $emulate;
    
    /**
     * $escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;
    
    /**
     * $baseDir
     *
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $baseDir;
    
    /**
     * $category
     *
     * @var \Magento\Catalog\Model\Category
     */
    protected $category;
    
    /**
     * $customer
     *
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;
    
    /**
     * $invoicePdf
     *
     * @var \Webkul\Marketplace\Model\Order\Pdf\Invoice
     */
    protected $invoicePdf;
    
    /**
     * $jsonHelper
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    
    /**
     * $creditmemo
     *
     * @var \Magento\Sales\Model\Order\Creditmemo
     */
    protected $creditmemo;
    
    /**
     * $reviewModel
     *
     * @var \Webkul\Marketplace\Model\Feedback
     */
    protected $reviewModel;
    
    /**
     * $imageHelper
     */
    protected $imageHelper;
    
    /**
     * $stockHelper
     *
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    protected $stockHelper;
    
    /**
     * $fileFactory
     *
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;
    
    /**
     * $shipmentPdf
     *
     * @var \Webkul\Marketplace\Model\Order\Pdf\Shipment
     */
    protected $shipmentPdf;
    
    /**
     * $transaction
     *
     * @var \Magento\Framework\DB\Transaction
     */
    protected $transaction;
    
    /**
     * $coreRegistry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;
    
    /**
     * $viewTemplate
     *
     * @var \Magento\Framework\View\Element\Template
     */
    protected $viewTemplate;
    
    /**
     * $eavAttribute
     *
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;
    
    /**
     * $productModel
     *
     * @var \Magento\Catalog\Model\Product
     */
    protected $productModel;
    
    /**
     * $feedBackModel
     *
     * @var \Webkul\Marketplace\Model\Feedbackcount
     */
    protected $feedBackModel;
    
    /**
     * $helperCatalog
     *
     * @var \Webkul\MobikulCore\Helper\Catalog
     */
    protected $helperCatalog;
    
    /**
     * $invoiceSender
     *
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;
    
    /**
     * $pdfCreditmemo
     *
     * @var \Webkul\Marketplace\Model\Order\Pdf\Creditmemo
     */
    protected $pdfCreditmemo;
    
    /**
     * $shipmentSender
     *
     * @var ShipmentSender
     */
    protected $shipmentSender;
    
    /**
     * $shipmentHelper
     *
     * @var \Magento\Shipping\Helper\Data
     */
    protected $shipmentHelper;
    
    /**
     * $orderViewBlock
     *
     * @var \Webkul\Marketplace\Block\Order\View
     */
    protected $orderViewBlock;
    
    /**
     * $checkoutHelper
     *
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;
    
    /**
     * $productFactory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    
    /**
     * $mediaDirectory
     */
    protected $mediaDirectory;
    
    /**
     * $saleperPartner
     *
     * @var \Webkul\Marketplace\Model\Saleperpartner
     */
    protected $saleperPartner;
    
    /**
     * $invoiceService
     *
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;
    
    /**
     * $creditmemoItem
     *
     * @var \Magento\Sales\Model\Order\Creditmemo\Item
     */
    protected $creditmemoItem;
    
    /**
     * $orderRepository
     *
     * @var OrderRepository
     */
    protected $orderRepository;
    
    /**
     * $dashboardHelper
     *
     * @var \Webkul\MobikulMp\Helper\Dashboard
     */
    protected $dashboardHelper;
    
    /**
     * $shipmentFactory
     *
     * @var Magento\Sales\Model\Order\ShipmentFactory
     */
    protected $shipmentFactory;
    
    /**
     * $customerSession
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    
    /**
     * $orderManagement
     *
     * @var OrderManagementInterface
     */
    protected $orderManagement;
    
    /**
     * $shipmentManager
     *
     * @var \Magento\Sales\Api\ShipmentManagementInterface
     */
    protected $shipmentManager;
    
    /**
     * $marketplaceBlock
     *
     * @var marketplaceBlock
     */
    protected $marketplaceBlock;
    
    /**
     * $sellerCollection
     *
     * @var \Webkul\Marketplace\Model\ResourceModel\Seller\Collection
     */
    protected $sellerCollection;
    
    /**
     * $creditmemoSender
     *
     * @var CreditmemoSender
     */
    protected $creditmemoSender;
    
    /**
     * $creditmemoManager
     *
     * @var \Magento\Sales\Api\CreditmemoManagementInterface
     */
    protected $creditmemoManager;
    
    /**
     * $invoiceCollection
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection
     */
    protected $invoiceCollection;
    
    /**
     * $invoiceRepository
     *
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;
    
    /**
     * $marketplaceOrders
     *
     * @var \Webkul\Marketplace\Model\Orders
     */
    protected $marketplaceOrders;
    
    /**
     * $sellerTransaction
     *
     * @var \Webkul\Marketplace\Model\Sellertransaction
     */
    protected $sellerTransaction;
    
    /**
     * $marketplaceHelper
     *
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;
    
    /**
     * $productRepository
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    
    /**
     * $creditmemoFactory
     *
     * @var CreditmemoFactory
     */
    protected $creditmemoFactory;
    
    /**
     * $invoiceManagement
     *
     * @var \Magento\Sales\Api\InvoiceManagementInterface
     */
    protected $invoiceManagement;
    
    /**
     * $shippingPopupBlock
     *
     * @var \Magento\Shipping\Block\Tracking\Popup
     */
    protected $shippingPopupBlock;
    
    /**
     * $shipmentCollection
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Collection
     */
    protected $shipmentCollection;
    
    /**
     * $marketplaceProduct
     *
     * @var \Webkul\Marketplace\Model\Product
     */
    protected $marketplaceProduct;
    
    /**
     * $fileUploaderFactory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $fileUploaderFactory;

    /**
     * \Magento\Weee\Block\Item\Price\Renderer
     */
    protected $priceRenderer;
    
    /**
     * $shippingInfoFactory
     *
     * @var \Magento\Shipping\Model\InfoFactory
     */
    protected $shippingInfoFactory;
    
    /**
     * $orderItemRepository
     *
     * @var \Magento\Sales\Model\Order\ItemRepository
     */
    protected $orderItemRepository;
    
    /**
     * $marketplaceSaleList
     *
     * @var \Webkul\Marketplace\Model\Saleslist
     */
    protected $marketplaceSaleList;
    
    /**
     * $creditmemoRepository
     *
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;
    
    /**
     * $marketplaceEmailHelper
     */
    protected $marketplaceEmailHelper;
    
    /**
     * $marketplaceOrderhelper
     */
    protected $marketplaceOrderhelper;
    
    /**
     * $orderCollectionFactory
     */
    protected $orderCollectionFactory;
    
    /**
     * \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer $orderItemRenderer
     */
    protected $orderItemRenderer;
    
    /**
     * $productCollectionFactory
     *
     * @var Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;
    
    /**
     * $countryCollectionFactory
     *
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $countryCollectionFactory;

    /**
     * \Webkul\MobikulApi\Block\Sales\Order\Totals
     */
    protected $orderTotal;
    
    /**
     * $marketplaceProductResource
     *
     * @var \Webkul\Marketplace\Model\Product
     */
    protected $marketplaceProductResource;
    
    /**
     * $sellerlistCollectionFactory
     *
     * @var \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory
     */
    protected $sellerlistCollectionFactory;

    /**
     * $transactionCollectionFactory
     *
     * @var TransactionCollectionFactory
     */
    protected $transactionCollectionFactory;
    
    /**
     * $sellerProductCollectionFactory
     *
     * @var SellerProduct
     */
    protected $sellerProductCollectionFactory;

    protected $toolBar;
    
    /**
     * $marketplaceOrderResourceCollection
     *
     * @var \Webkul\Marketplace\Model\ResourceModel\Orders\Collection
     */
    protected $marketplaceOrderResourceCollection;

    /**
     * @var \Webkul\Marketplace\Block\Transaction\Withdrawal
     */
    protected $transactionWithdrawalBlock;

    /**
     * Construct function for abstract class AbstractMarketplace
     *
     * @param Context                                                        $context                            context
     * @param Emulation                                                      $emulate                            emulate
     * @param Registry                                                  $coreRegistry                       coreRegistry
     * @param Filesystem                                                  $filesystem                         filesystem
     * @param InvoiceSender                                            $invoiceSender                      invoiceSender
     * @param ShipmentSender                                          $shipmentSender                     shipmentSender
     * @param OrderRepository                                        $orderRepository                    orderRepository
     * @param ShipmentFactory                                        $shipmentFactory                    shipmentFactory
     * @param \Magento\Sales\Model\Order                                       $order                              order
     * @param CreditmemoSender                                      $creditmemoSender                   creditmemoSender
     * @param \Magento\Framework\Escaper                                    $escaper                            escaper
     * @param CreditmemoFactory                                    $creditmemoFactory                  CreditmemoFactory
     * @param \Webkul\MobikulCore\Helper\Data                                $helper                             helper
     * @param \Webkul\Marketplace\Model\Seller                                $seller                             seller
     * @param CollectionFactory                               $orderCollectionFactory             orderCollectionFactory
     * @param \Magento\Catalog\Model\Category                               $category                           category
     * @param OrderManagementInterface                              $orderManagement                    orderManagement
     * @param \Webkul\Markteplace\Seller\Model                            $mpSeller                           $mpSeller
     * @param \Magento\Customer\Model\Customer                             $customer                           customer
     * @param \Magento\Catalog\Model\Product                            $productModel                       productModel
     * @param SellerProduct                           $sellerProductCollectionFactory     sellerProductCollectionFactory
     * @param \Magento\Checkout\Helper\Data                          $checkoutHelper                     checkoutHelper
     * @param \Magento\Shipping\Helper\Data                            shipmentHelper                     shipmentHelper
     * @param \Magento\Framework\DB\Transaction                         $transaction                        transaction
     * @param \Magento\FrameworkeEventManager                         $eventManager                       $eventManager
     * @param \Webkul\Marketplace\Model\Feedback                         $reviewModel                        reviewModel
     * @param MageProductCollection                         $productCollectionFactory           productCollectionFactory
     * @param \Magento\Framework\Json\Helper\Data                         $jsonHelper                         jsonHelper
     * @param \Magento\Customer\Model\Session                      $customerSession                    customerSession
     * @param \Magento\Sales\Model\Order\Shipment\Track                      $track                              track
     * @param \Webkul\MobikulCore\Helper\Catalog                       $helperCatalog                      helperCatalog
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                     $date                               date
     * @param \Magento\Sales\Model\Order\Creditmemo                      $creditmemo                         creditmemo
     * @param \Webkul\Marketplace\Helper\Data                      $marketplaceHelper                  marketplaceHelper
     * @param CreditmemoRepositoryInterface                     $creditmemoRepository               creditmemoRepository
     * @param \Magento\CatalogInventory\Helper\Stock                     $stockHelper                        stockHelper
     * @param \Webkul\Marketplace\Model\Orders                     $marketplaceOrders                  marketplaceOrders
     * @param \Webkul\MobikulMp\Helper\Dashboard                    $dashboardHelper                    dashboardHelper
     * @param \Webkul\Marketplace\Block\Order\View                   $orderViewBlock                     orderViewBlock
     * @param \Magento\Framework\Filesystem\DirectoryList                   $baseDir                            baseDir
     * @param \Magento\Catalog\Model\ProductFactory                  $productFactory                     productFactory
     * @param \Webkul\Marketplace\Model\Product                   $marketplaceProduct                 marketplaceProduct
     * @param \Webkul\Marketplace\Model\Feedbackcount                  $feedBackModel                      feedBackModel
     * @param \Magento\Catalog\Block\Product\Context                  $productContext                     productContext
     * @param \Magento\Weee\Block\Item\Price\Renderer                 $priceRenderer                      priceRenderer
     * @param \Magento\Framework\View\Element\Template                 $viewTemplate                       viewTemplate
     * @param \Webkul\Marketplace\Model\Order\Pdf\Invoice                 $invoicePdf                         invoicePdf
     * @param \Webkul\Marketplace\Block\Marketplace               $marketplaceBlock                   marketplaceBlock
     * @param \Webkul\Marketplace\Helper\Email                $marketplaceEmailHelper             marketplaceEmailHelper
     * @param \Webkul\Marketplace\Model\Saleperpartner                $saleperPartner                     saleperPartner
     * @param \Webkul\MobikulApi\Block\Sales\Order\Totals                $orderTotals                        orderTotals
     * @param \Webkul\Marketplace\Model\Saleslist                $marketplaceSaleList                marketplaceSaleList
     * @param \Magento\Shipping\Model\InfoFactory               $shippingInfoFactory                shippingInfoFactory
     * @param \Webkul\Marketplace\Model\Order\Pdf\Shipment              $shipmentPdf                        shipmentPdf
     * @param \Webkul\Marketplace\Helper\Orders              $marketplaceOrderhelper             marketplaceOrderhelper
     * @param \Magento\Shipping\Block\Tracking\Popup             $shippingPopupBlock                 shippingPopupBlock
     * @param TransactionCollectionFactory              $transactionCollectionFactory       transactionCollectionFactory
     * @param \Magento\Sales\Model\Order\Creditmemo\Item           $creditmemoItem                     creditmemoItem
     * @param \Magento\Sales\Model\Service\InvoiceService             $invoiceService                     invoiceService
     * @param \Webkul\Marketplace\Model\Order\Pdf\Creditmemo           $pdfCreditmemo                      pdfCreditmemo
     * @param \Magento\Framework\App\Response\Http\FileFactory         $fileFactory                        fileFactory
     * @param \Webkul\Marketplace\Model\Sellertransaction          $sellerTransaction                  sellerTransaction
     * @param \Magento\Sales\Model\Order\ItemRepository          $orderItemRepository                orderItemRepository
     * @param \Magento\Sales\Api\ShipmentManagementInterface        $shipmentManager                    shipmentManager
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute        $eavAttribute                       eavAttribute
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface        $invoiceRepository                  invoiceRepository
     * @param \Magento\Sales\Api\InvoiceManagementInterface        $invoiceManagement                  invoiceManagement
     * @param \Webkul\MobikulApi\Block\Sales\Order\Invoice\Totals    $invoiceTotals                      invoiceTotals
     * @param \Magento\Catalog\Api\ProductRepositoryInterface      $productRepository                  productRepository
     * @param \Magento\Sales\Api\CreditmemoManagementInterface     $creditmemoManager                  creditmemoManager
     * @param \Magento\MediaStorage\Model\File\UploaderFactory   $fileUploaderFactory                fileUploaderFactory
     * @param \Magento\Weee\Block\Adminhtml\Items\Price\Renderer  $adminPriceRenderer                 adminPriceRenderer
     * @param \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer       $orderItemRenderer
     * orderItemRenderer
     * @param \Webkul\Marketplace\Model\ResourceModel\Seller\Collection        $sellerCollection
     * sellerCollection
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection      $invoiceCollection
     * invoiceCollection
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Collection     $shipmentCollection
     * shipmentCollection
     * @param \Magento\Sales\Block\Adminhtml\Items\Renderer\DefaultRenderer    $invoiceItemRenderer
     * invoiceItemRenderer
     * @param \Webkul\Marketplace\Model\ResourceModel\Product\Collection       $marketplaceProductResource
     * marketplaceProductResource
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * countryCollectionFactory
     * @param \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory $sellerlistCollectionFactory
     * sellerlistCollectionFactory
     * @param \Webkul\Marketplace\Model\ResourceModel\Orders\Collection        $marketplaceOrderResourceCollection
     * marketplaceOrderResourceCollection
     * @param \Magento\Store\Model\Store $store
     */
    public function __construct(
        Context $context,
        Emulation $emulate,
        Registry $coreRegistry,
        Filesystem $filesystem,
        InvoiceSender $invoiceSender,
        ShipmentSender $shipmentSender,
        OrderRepository $orderRepository,
        ShipmentFactory $shipmentFactory,
        \Magento\Sales\Model\Order $order,
        CreditmemoSender $creditmemoSender,
        \Magento\Framework\Escaper $escaper,
        CreditmemoFactory $creditmemoFactory,
        \Webkul\MobikulCore\Helper\Data $helper,
        \Webkul\Marketplace\Model\Seller $seller,
        CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Model\Category $category,
        OrderManagementInterface $orderManagement,
        \Webkul\Marketplace\Model\Seller $mpSeller,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Catalog\Model\Product $productModel,
        SellerProduct $sellerProductCollectionFactory,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Shipping\Helper\Data $shipmentHelper,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Framework\Event\Manager $eventManager,
        \Webkul\Marketplace\Model\Feedback $reviewModel,
        MageProductCollection $productCollectionFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Shipment\Track $track,
        \Webkul\MobikulCore\Helper\Catalog $helperCatalog,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Sales\Model\Order\Creditmemo $creditmemo,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\CatalogInventory\Helper\Stock $stockHelper,
        \Webkul\Marketplace\Model\Orders $marketplaceOrders,
        \Webkul\MobikulMp\Helper\Dashboard $dashboardHelper,
        \Webkul\Marketplace\Block\Order\View $orderViewBlock,
        \Magento\Framework\Filesystem\DirectoryList $baseDir,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\Marketplace\Model\Product $marketplaceProduct,
        \Webkul\Marketplace\Model\Feedbackcount $feedBackModel,
        \Magento\Catalog\Block\Product\Context $productContext,
        \Magento\Weee\Block\Item\Price\Renderer $priceRenderer,
        \Magento\Framework\View\Element\Template $viewTemplate,
        \Webkul\Marketplace\Model\Order\Pdf\Invoice $invoicePdf,
        \Webkul\Marketplace\Block\Marketplace $marketplaceBlock,
        \Webkul\Marketplace\Helper\Email $marketplaceEmailHelper,
        \Webkul\Marketplace\Model\Saleperpartner $saleperPartner,
        \Webkul\MobikulApi\Block\Sales\Order\Totals $orderTotals,
        \Webkul\Marketplace\Model\Saleslist $marketplaceSaleList,
        \Magento\Shipping\Model\InfoFactory $shippingInfoFactory,
        \Magento\Catalog\Block\Product\ProductList\Toolbar $toolBar,
        \Webkul\Marketplace\Model\Order\Pdf\Shipment $shipmentPdf,
        \Webkul\Marketplace\Helper\Orders $marketplaceOrderhelper,
        \Magento\Shipping\Block\Tracking\Popup $shippingPopupBlock,
        TransactionCollectionFactory $transactionCollectionFactory,
        \Magento\Sales\Model\Order\Creditmemo\Item $creditmemoItem,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Webkul\Marketplace\Model\Order\Pdf\Creditmemo $pdfCreditmemo,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Webkul\Marketplace\Model\Sellertransaction $sellerTransaction,
        \Magento\Sales\Model\Order\ItemRepository $orderItemRepository,
        \Magento\Sales\Api\ShipmentManagementInterface $shipmentManager,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement,
        \Webkul\MobikulApi\Block\Sales\Order\Invoice\Totals $invoiceTotals,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManager,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Weee\Block\Adminhtml\Items\Price\Renderer $adminPriceRenderer,
        \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer $orderItemRenderer,
        \Webkul\Marketplace\Model\ResourceModel\Seller\Collection $sellerCollection,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection $invoiceCollection,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Collection $shipmentCollection,
        \Magento\Sales\Block\Adminhtml\Items\Renderer\DefaultRenderer $invoiceItemRenderer,
        \Webkul\Marketplace\Model\ResourceModel\Product\Collection $marketplaceProductResource,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory $sellerlistCollectionFactory,
        \Webkul\Marketplace\Model\ResourceModel\Orders\Collection $marketplaceOrderResourceCollection,
        \Webkul\Marketplace\Block\Transaction\Withdrawal $transactionWithdrawalBlock,
        \Webkul\Marketplace\Model\ResourceModel\Saleperpartner\CollectionFactory $partnerCollectionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Store\Model\Store $store,
        // ProductFlags $productFlags,
        // ProductFlagReason $productFlagReason,
        // SellerFlags $sellerFlags,
        // SellerFlagReason $sellerFlagReason,
        \Magento\Framework\Filesystem\Driver\File $fileDriver
    ) {
        $this->date                               = $date;
        $this->order                              = $order;
        $this->toolBar                            = $toolBar;
        $this->track                              = $track;
        $this->helper                             = $helper;
        $this->seller                             = $seller;
        $this->emulate                            = $emulate;
        $this->escaper                            = $escaper;
        $this->baseDir                            = $baseDir;
        $this->category                           = $category;
        $this->customer                           = $customer;
        $this->mpSeller                           = $mpSeller;
        $this->creditmemo                         = $creditmemo;
        $this->jsonHelper                         = $jsonHelper;
        $this->invoicePdf                         = $invoicePdf;
        $this->imageHelper                        = $productContext->getImageHelper();
        $this->stockHelper                        = $stockHelper;
        $this->shipmentPdf                        = $shipmentPdf;
        $this->reviewModel                        = $reviewModel;
        $this->fileFactory                        = $fileFactory;
        $this->transaction                        = $transaction;
        $this->coreRegistry                       = $coreRegistry;
        $this->productModel                       = $productModel;
        $this->orderTotals                        = $orderTotals;
        $this->eventManager                       = $eventManager;
        $this->eavAttribute                       = $eavAttribute;
        $this->viewTemplate                       = $viewTemplate;
        $this->feedBackModel                      = $feedBackModel;
        $this->invoiceSender                      = $invoiceSender;
        $this->helperCatalog                      = $helperCatalog;
        $this->pdfCreditmemo                      = $pdfCreditmemo;
        $this->priceRenderer                      = $priceRenderer;
        $this->invoiceTotals                      = $invoiceTotals;
        $this->creditmemoItem                     = $creditmemoItem;
        $this->invoiceService                     = $invoiceService;
        $this->shipmentSender                     = $shipmentSender;
        $this->checkoutHelper                     = $checkoutHelper;
        $this->productFactory                     = $productFactory;
        $this->shipmentHelper                     = $shipmentHelper;
        $this->saleperPartner                     = $saleperPartner;
        $this->orderViewBlock                     = $orderViewBlock;
        $this->mediaDirectory                     = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->shipmentManager                    = $shipmentManager;
        $this->orderRepository                    = $orderRepository;
        $this->customerSession                    = $customerSession;
        $this->shipmentFactory                    = $shipmentFactory;
        $this->dashboardHelper                    = $dashboardHelper;
        $this->orderManagement                    = $orderManagement;
        $this->marketplaceBlock                   = $marketplaceBlock;
        $this->sellerCollection                   = $sellerCollection;
        $this->creditmemoSender                   = $creditmemoSender;
        $this->creditmemoFactory                  = $creditmemoFactory;
        $this->creditmemoManager                  = $creditmemoManager;
        $this->invoiceRepository                  = $invoiceRepository;
        $this->marketplaceOrders                  = $marketplaceOrders;
        $this->productRepository                  = $productRepository;
        $this->sellerTransaction                  = $sellerTransaction;
        $this->invoiceCollection                  = $invoiceCollection;
        $this->marketplaceHelper                  = $marketplaceHelper;
        $this->orderItemRenderer                  = $orderItemRenderer;
        $this->invoiceManagement                  = $invoiceManagement;
        $this->adminPriceRenderer                 = $adminPriceRenderer;
        $this->shippingPopupBlock                 = $shippingPopupBlock;
        $this->marketplaceProduct                 = $marketplaceProduct;
        $this->shipmentCollection                 = $shipmentCollection;
        $this->invoiceItemRenderer                = $invoiceItemRenderer;
        $this->marketplaceSaleList                = $marketplaceSaleList;
        $this->shippingInfoFactory                = $shippingInfoFactory;
        $this->fileUploaderFactory                = $fileUploaderFactory;
        $this->orderItemRepository                = $orderItemRepository;
        $this->creditmemoRepository               = $creditmemoRepository;
        $this->marketplaceEmailHelper             = $marketplaceEmailHelper;
        $this->marketplaceOrderhelper             = $marketplaceOrderhelper;
        $this->orderCollectionFactory             = $orderCollectionFactory;
        $this->countryCollectionFactory           = $countryCollectionFactory;
        $this->productCollectionFactory           = $productCollectionFactory;
        $this->marketplaceProductResource         = $marketplaceProductResource;
        $this->sellerlistCollectionFactory        = $sellerlistCollectionFactory;
        $this->transactionCollectionFactory       = $transactionCollectionFactory;
        $this->sellerProductCollectionFactory     = $sellerProductCollectionFactory;
        $this->marketplaceOrderResourceCollection = $marketplaceOrderResourceCollection;
        $this->transactionWithdrawalBlock         = $transactionWithdrawalBlock;
        $this->partnerCollectionFactory           = $partnerCollectionFactory;
        $this->customerRepository                 = $customerRepository;
        $this->store = $store;
        // $this->productFlags = $productFlags;
        // $this->productFlagReason = $productFlagReason;
        // $this->sellerFlags = $sellerFlags;
        // $this->sellerFlagReason = $sellerFlagReason;
        $this->fileDriver = $fileDriver;
        parent::__construct($helper, $context, $jsonHelper);
    }

    /**
     * Function to get product Data from Product Id
     *
     * @param int $id product id
     *
     * @return \Magento\Catalog\Model\ProductFactory
     */
    public function getProductData($id)
    {
        return $this->productFactory->create()->load($id);
    }

    /**
     * Function to get Marketplace product's sales details
     *
     * @param int $productId product id
     *
     * @return array $sales information of a product
     */
    public function getSalesdetail($productId = "")
    {
        $data = [
            "clearedat"             => 0,
            "amountearned"          => 0,
            "quantitysold"          => 0,
            "quantitysoldpending"   => 0,
            "quantitysoldconfirmed" => 0
        ];
        $arr = [];
        $quantity = $this->marketplaceSaleList
            ->getCollection()
            ->addFieldToFilter("mageproduct_id", $productId);
        foreach ($quantity as $rec) {
            $status = $rec->getCpprostatus();
            $data["quantitysold"] = $data["quantitysold"] + $rec->getMagequantity();
            if ($status == 1) {
                $data["quantitysoldconfirmed"] = $data["quantitysoldconfirmed"] + $rec->getMagequantity();
            } else {
                $data["quantitysoldpending"] = $data["quantitysoldpending"] + $rec->getMagequantity();
            }
        }
        $amountearned = $this->marketplaceSaleList
            ->getCollection()
            ->addFieldToFilter("cpprostatus", \Webkul\Marketplace\Model\Saleslist::PAID_STATUS_PENDING)
            ->addFieldToFilter("mageproduct_id", $productId);
        foreach ($amountearned as $rec) {
            $data["amountearned"] = $data["amountearned"] + $rec["actual_seller_amount"];
            $arr[] = $rec["created_at"];
        }
        $data["created_at"] = $arr;
        return $data;
    }
    
    /**
     * Initialize shipment model instance
     *
     * @param int                        $shipmentId shipment id
     * @param \Magento\Sales\Model\Order $order      order
     *
     * @return \Magento\Sales\Model\Order\Shipment|false
     */
    protected function _initShipment($shipmentId, $order)
    {
        $data = [];
        $data['success'] = false;
        if (!$shipmentId) {
            return $data;
        }
        $shipment = $this->shipmentFactory->create($order)->load($shipmentId);
        if (!$shipment) {
            return $data;
        }
        try {
            $tracking = $this->marketplaceOrderhelper->getOrderinfo($order->getId());
            if ($tracking && $tracking->getId()) {
                if ($tracking->getShipmentId() == $shipmentId) {
                    if (!$shipmentId) {
                        $data['message'] = __('The shipment no longer exists.');
                        return $data;
                    }
                } else {
                    $data['message'] = __('You are not authorize to view this shipment.');
                    return $data;
                }
            } else {
                $data['message'] = __('You are not authorize to view this shipment.');
                return $data;
            }
        } catch (\NoSuchEntityException $e) {
            return $data;
        } catch (\InputException $e) {
            return $data;
        }
        $this->coreRegistry->register('sales_order', $order);
        $this->coreRegistry->register('current_order', $order);
        $this->coreRegistry->register('current_shipment', $shipment);
        $data['success'] = true;
        $data['shipment'] = $shipment;
        $data['tracking'] = $tracking;
        return $data;
    }

    /**
     * Initialize order model instance.
     *
     * @param \Magento\Sales\Model\Order $order order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface|falses
     */
    public function _initOrder($order)
    {
        $data = [];
        $data['success'] = false;
        try {
            $tracking = $this->marketplaceOrderhelper->getOrderinfo($order->getId());
            if ($tracking && $tracking->getId()) {
                if ($tracking->getOrderId() == $order->getId()) {
                    if (!$order->getId()) {
                        $data['message'] = __('This order no longer exists.');
                        return $data;
                    }
                } else {
                    $data['message'] = __('You are not authorize to manage this order.');
                    return $data;
                }
            } else {
                $data['message'] = __('You are not authorize to manage this order.');
                return $data;
            }
        } catch (\NoSuchEntityException $e) {
            $data['message'] = __('This order no longer exists.');
            return $data;
        } catch (\InputException $e) {
            $data['message'] = __('This order no longer exists.');
            return $data;
        }
        $this->coreRegistry->register('sales_order', $order);
        $this->coreRegistry->register('current_order', $order);
        $data['success'] = true;
        return $data;
    }
}
