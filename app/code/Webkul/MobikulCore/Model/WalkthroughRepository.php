<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\MobikulCore\Api\Data\WalkthroughInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

/**
 * Class WalkthroughRepository model
 */
class WalkthroughRepository implements \Webkul\MobikulCore\Api\WalkthroughRepositoryInterface
{

    protected $_resourceModel;
    protected $_instances = [];
    protected $_collectionFactory;
    protected $_instancesById = [];
    protected $_carouselimageFactory;
    protected $_extensibleDataObjectConverter;

    public function __construct(
        WalkthroughFactory $walkthroughFactory,
        ResourceModel\Walkthrough $resourceModel,
        ResourceModel\Walkthrough\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_collectionFactory = $collectionFactory;
        $this->_walkthroughFactory = $walkthroughFactory;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    public function save(WalkthroughInterface $walkthrough)
    {
        $walkthroughId = $walkthrough->getId();
        try {
            $this->_resourceModel->save($walkthrough);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
        }
        unset($this->_instancesById[$walkthrough->getId()]);
        return $this->getById($walkthrough->getId());
    }

    public function getById($walkthroughId)
    {
        $walkthroughData = $this->_walkthroughFactory->create();
        $walkthroughData->load($walkthroughId);
        $this->_instancesById[$walkthroughId] = $walkthroughData;
        return $this->_instancesById[$walkthroughId];
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->_collectionFactory->create();
        $collection->load();
        return $collection;
    }

    public function delete(WalkthroughInterface $walkthrough)
    {
        $walkthroughId = $walkthrough->getId();
        try {
            $this->_resourceModel->delete($walkthrough);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __("Unable to remove walk through with id %1", $walkthroughId)
            );
        }
        unset($this->_instancesById[$walkthroughId]);
        return true;
    }

    public function deleteById($walkthroughId)
    {
        $walkthrough = $this->getById($walkthroughId);
        return $this->delete($walkthrough);
    }
}
