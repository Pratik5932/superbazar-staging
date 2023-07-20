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

namespace Webkul\MobikulMp\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Store\Model\App\Emulation;
use Webkul\MobikulMp\Ui\DataProvider\Product\RelatedUpsellCrosssellDataProvider;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Webkul\Marketplace\Controller\Product\SaveProduct;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\MediaStorage\Model\ResourceModel\File\Storage\File;

/**
 * Abstract Class AbstractProduct
 * @author   Webkul <support@webkul.com>
 * @license  https://store.webkul.com/license.html ASL Licence
 * @link     https://store.webkul.com/license.html
 */
abstract class AbstractProduct extends \Webkul\MobikulApi\Controller\ApiController
{
    
    /**
     * $curl
     *
     * @var Curl
     */
    protected $curl;
    
    /**
     * $link
     *
     * @var \Magento\Downloadable\Model\Link
     */
    protected $link;
    
    /**
     * $status
     *
     * @var Status
     */
    protected $status;
    
    /**
     * $helper
     *
     * @var \Webkul\MobikulCore\Helper\Data
     */
    protected $helper;
    
    /**
     * $sample
     *
     * @var \Magento\Downloadable\Model\Sample
     */
    protected $sample;
    
    /**
     * $product
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $product;
    
    /**
     * $emulate
     *
     * @var Emulation
     */
    protected $emulate;
    
    /**
     * $category
     *
     * @var \Magento\Catalog\Model\Category
     */
    protected $category;
    
    /**
     * $linksBlock
     *
     * @var \Webkul\Marketplace\Block\Product\Edit\Downloadable\Links
     */
    protected $linksBlock;
    
    /**
     * $fileHelper
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $fileHelper;
    
    /**
     * $productType
     *
     * @var productType
     */
    protected $productType;
    
    /**
     * $productUrl
     *
     * @var \Magento\Catalog\Model\Product\Url
     */
    protected $productUrl;
    
    /**
     * $jsonHelper
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    
    /**
     * $fileUtility
     *
     * @var File
     */
    protected $fileUtility;
    
    /**
     * $mediaConfig
     *
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $mediaConfig;
    
    /**
     * $saveProduct
     *
     * @var SaveProduct
     */
    protected $saveProduct;
    
    /**
     * $priceFormat
     *
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceFormat;
    
    /**
     * $entityModel
     *
     * @var \Magento\Eav\Model\Entity
     */
    protected $entityModel;
    
    /**
     * $attributeSet
     *
     * @var \Magento\Eav\Api\AttributeSetRepositoryInterface
     */
    protected $attributeSet;
    
    /**
     * $imageAdapter
     *
     * @var AdapterFactory
     */
    protected $imageAdapter;
    
    /**
     * $categoryTree
     *
     * @var \Webkul\MobikulCore\Model\Category\Tree
     */
    protected $categoryTree;
    
    /**
     * $samplesBlock
     *
     * @var \Webkul\Marketplace\Block\Product\Edit\Downloadable\Samples
     */
    protected $samplesBlock;
    
    /**
     * $catalogHelper
     *
     * @var \Webkul\MobikulCore\Helper\Catalog
     */
    protected $catalogHelper;
    
    /**
     * $productBuilder
     *
     * @var \Webkul\Marketplace\Controller\Product\Builder
     */
    protected $productBuilder;
    
    /**
     * $mediaDirectory
     */
    protected $mediaDirectory;
    
    /**
     * $customerSession
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    
    /**
     * $attributeModel
     *
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected $attributeModel;
    
    /**
     * $productCollection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollection;
    
    /**
     * $mpMobikulHelper
     *
     * @var \Webkul\MobikulMp\Helper\Data
     */
    protected $mpMobikulHelper;
    
    /**
     * $productRepository
     *
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;
    
    /**
     * $databaseStorage
     *
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $databaseStorage;
    
    /**
     * $attributeSetOptions
     *
     * @var \Magento\Catalog\Model\Product\AttributeSet\Options
     */
    protected $attributeSetOptions;
    
    /**
     * $marketplaceHelper
     *
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;
    
    /**
     * $relatedProDataProvider
     *
     * @var RelatedUpsellCrosssellDataProvider
     */
    protected $relatedProDataProvider;
    
    /**
     * $fileUploaderFactory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $fileUploaderFactory;
    
    /**
     * $productResourceModel
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResourceModel;

    /**
     * Construct function for abstract class AbstractProduct
     *
     * @param Curl                                                           $curl                       curl
     * @param Status                                                         $status                     status
     * @param Context                                                        $context                    context
     * @param File                                                           $fileUtility                fileUtility
     * @param Emulation                                                      $emulate                    emulate
     * @param Filesystem                                                     $filesystem                 filesystem
     * @param SaveProduct                                                    $saveProduct                saveProduct
     * @param AdapterFactory                                                 $imageAdapterFactory
     * imageAdapterFactory
     * @param \Magento\Eav\Model\Entity                                      $entityModel                entityModel
     * @param \Magento\Downloadable\Model\Link                               $link                       link
     * @param \Webkul\MobikulCore\Helper\Data                                $helper                     helper
     * @param \Magento\Catalog\Model\Category                                $category                   category
     * @param \Magento\Downloadable\Model\Sample                             $sample                     sample
     * @param \Magento\Downloadable\Helper\File                              $fileHelper                 fileHelper
     * @param \Magento\Catalog\Model\ProductFactory                          $product                    product
     * @param \Magento\Catalog\Model\Product\Url                             $productUrl                 productUrl
     * @param \Webkul\MobikulMp\Helper\Data                                  $mpMobikulHelper            mpMobikulHelper
     * @param \Magento\Framework\Json\Helper\Data                            $jsonHelper                 jsonHelper
     * @param \Magento\Catalog\Model\Product\Type                            $productType                productType
     * @param \Magento\Customer\Model\Session                                $customerSession            customerSession
     * @param \Webkul\MobikulCore\Helper\Catalog                             $catalogHelper              catalogHelper
     * @param \Webkul\Marketplace\Helper\Data                                $marketplaceHelper
     * marketplaceHelper
     * @param \Magento\Framework\Pricing\Helper\Data                         $priceFormat                priceFormat
     * @param \Webkul\MobikulCore\Model\Category\Tree                        $categoryTree               categoryTree
     * @param \Magento\Catalog\Model\Product\Media\Config                    $mediaConfig                mediaConfig
     * @param \Magento\Catalog\Model\ProductRepository                       $productRepository
     * productRepository
     * @param RelatedUpsellCrosssellDataProvider                             $relatedProductDataProvider
     * relatedProductDataProvider
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface               $attributeSet               attributeSet
     * @param \Webkul\Marketplace\Controller\Product\Builder                 $productBuilder             productBuilder
     * @param \Magento\Catalog\Model\ResourceModel\Product                   $productResourceModel
     * productResourceModel
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute             $attributeModel             attributeModel
     * @param \Magento\MediaStorage\Helper\File\Storage\Database             $databaseStorage            databaseStorage
     * @param \Webkul\Marketplace\Block\Product\Edit\Downloadable\Links      $linksBlock                 linksBlock
     * @param \Magento\MediaStorage\Model\File\UploaderFactory               $fileUploaderFactory
     * fileUploaderFactory
     * @param \Magento\Catalog\Model\Product\AttributeSet\Options            $attributeSetOptions
     * attributeSetOptions
     * @param \Webkul\Marketplace\Block\Product\Edit\Downloadable\Samples    $samplesBlock               samplesBlock
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
     * productCollection
     */
    public function __construct(
        Curl $curl,
        Status $status,
        Context $context,
        File $fileUtility,
        Emulation $emulate,
        Filesystem $filesystem,
        SaveProduct $saveProduct,
        AdapterFactory $imageAdapterFactory,
        \Magento\Eav\Model\Entity $entityModel,
        \Magento\Downloadable\Model\Link $link,
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Catalog\Model\Category $category,
        \Magento\Downloadable\Model\Sample $sample,
        \Magento\Downloadable\Helper\File $fileHelper,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Catalog\Model\Product\Url $productUrl,
        \Webkul\MobikulMp\Helper\Data $mpMobikulHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\Product\Type $productType,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MobikulCore\Helper\Catalog $catalogHelper,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Framework\Pricing\Helper\Data $priceFormat,
        \Webkul\MobikulCore\Model\Category\Tree $categoryTree,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        RelatedUpsellCrosssellDataProvider $relatedProductDataProvider,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet,
        \Webkul\Marketplace\Controller\Product\Builder $productBuilder,
        \Magento\Catalog\Model\ResourceModel\Product $productResourceModel,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeModel,
        \Magento\MediaStorage\Helper\File\Storage\Database $databaseStorage,
        \Webkul\Marketplace\Block\Product\Edit\Downloadable\Links $linksBlock,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Catalog\Model\Product\AttributeSet\Options $attributeSetOptions,
        \Webkul\Marketplace\Block\Product\Edit\Downloadable\Samples $samplesBlock,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Framework\Filesystem\Io\File $fileIo
    ) {
        $this->curl                   = $curl;
        $this->link                   = $link;
        $this->status                 = $status;
        $this->helper                 = $helper;
        $this->sample                 = $sample;
        $this->emulate                = $emulate;
        $this->product                = $product;
        $this->category               = $category;
        $this->fileHelper             = $fileHelper;
        $this->jsonHelper             = $jsonHelper;
        $this->productUrl             = $productUrl;
        $this->fileSystem             = $filesystem;
        $this->productType            = $productType;
        $this->linksBlock             = $linksBlock;
        $this->saveProduct            = $saveProduct;
        $this->mediaConfig            = $mediaConfig;
        $this->attributeSet           = $attributeSet;
        $this->fileUtility            = $fileUtility;
        $this->priceFormat            = $priceFormat;
        $this->entityModel            = $entityModel;
        $this->categoryTree           = $categoryTree;
        $this->samplesBlock           = $samplesBlock;
        $this->productBuilder         = $productBuilder;
        $this->imageAdapter           = $imageAdapterFactory->create();
        $this->mediaDirectory         = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->catalogHelper          = $catalogHelper;
        $this->attributeModel         = $attributeModel;
        $this->mpMobikulHelper        = $mpMobikulHelper;
        $this->customerSession        = $customerSession;
        $this->databaseStorage        = $databaseStorage;
        $this->marketplaceHelper      = $marketplaceHelper;
        $this->productCollection      = $productCollection;
        $this->productRepository      = $productRepository;
        $this->attributeSetOptions    = $attributeSetOptions;
        $this->fileUploaderFactory    = $fileUploaderFactory;
        $this->productResourceModel   = $productResourceModel;
        $this->relatedProDataProvider = $relatedProductDataProvider;
        $this->fileIo = $fileIo;
        parent::__construct($helper, $context, $jsonHelper);
    }
}
