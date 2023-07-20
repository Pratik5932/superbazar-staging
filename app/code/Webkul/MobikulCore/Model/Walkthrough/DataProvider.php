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

namespace Webkul\MobikulCore\Model\Walkthrough;

use Magento\Eav\Model\Config;
use Magento\Framework\App\ObjectManager;
use Webkul\MobikulCore\Model\Walkthrough;
use Magento\Framework\Session\SessionManagerInterface;
use Webkul\MobikulCore\Model\ResourceModel\Walkthrough\Collection;
use Webkul\MobikulCore\Model\ResourceModel\Walkthrough\CollectionFactory as WalkthroughCollectionFactory;

/**
 * Class DataProvider model
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $session;
    protected $collection;
    protected $loadedData;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        WalkthroughCollectionFactory $walkthroughCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $walkthroughCollectionFactory->create();
        $this->collection->addFieldToSelect("*");
    }

    protected function getSession()
    {
        if ($this->session === null) {
            $this->session = ObjectManager::getInstance()
            ->get(\Magento\Framework\Session\SessionManagerInterface::class);
        }
        return $this->session;
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $walkthrough) {
            $result["walkthrough"] = $walkthrough->getData();
            $this->loadedData[$walkthrough->getId()] = $result;
        }
        $data = $this->getSession()->getWalkthroughFormData();
        if (!empty($data)) {
            $walkthroughId = $data["mobikul_walkthrough"]["id"] ?? null;
            $this->loadedData[$walkthroughId] = $data;
            $this->getSession()->unsWalkthroughFormData();
        }
        return $this->loadedData;
    }
}
