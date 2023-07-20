<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rewards
 * @version   3.0.57
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\RewardsAdminUi\Ui\Tier\Form;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Mirasvit\Rewards\Api\Data\TierInterface;
use Mirasvit\Rewards\Helper\Storeview;
use Mirasvit\Rewards\Model\ResourceModel\Tier\CollectionFactory;
use Mirasvit\Rewards\Model\Tier\Backend\FileProcessor;

class DataProvider extends AbstractDataProvider
{
    private $storeviewHelper;
    private $request;
    private $url;
    private $pool;
    private $fileProcessor;

    /**
     * DataProvider constructor.
     * @param Storeview $storeviewHelper
     * @param RequestInterface $request
     * @param UrlInterface $url
     * @param CollectionFactory $collectionFactory
     * @param FileProcessor $fileProcessor
     * @param PoolInterface $pool
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        Storeview $storeviewHelper,
        RequestInterface $request,
        UrlInterface $url,
        CollectionFactory $collectionFactory,
        FileProcessor $fileProcessor,
        PoolInterface $pool,
        $name ,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->storeviewHelper = $storeviewHelper;
        $this->request         = $request;
        $this->url             = $url;
        $this->pool            = $pool;
        $this->fileProcessor   = $fileProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigData()
    {
        $config = parent::getConfigData();

        $config['submit_url'] = $this->url->getUrl(
            'rewards/tier/save',
            [
                'id'    => (int) $this->request->getParam('id'),
                'store' => (int) $this->request->getParam('store'),
            ]
        );

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() == 'tier_id') {
            $filter->setField('main_table.tier_id');
        }

        parent::addFilter($filter);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $result = [];

        foreach ($this->collection as $item) {
            $result[$item->getId()] = $item->getData();

            $storeId = (int) $this->request->getParam('store');
            $item->setStoreId($storeId);
            $name = $this->storeviewHelper->getStoreViewValue($item, TierInterface::KEY_NAME);
            $description = $this->storeviewHelper->getStoreViewValue($item, TierInterface::KEY_DESCRIPTION);
            $templateId = $this->storeviewHelper->getStoreViewValue($item, TierInterface::KEY_TEMPLATE_ID);
            $logo = $this->storeviewHelper->getStoreViewValue($item, TierInterface::KEY_TIER_LOGO);
            $item->unsetData('store_id');
            $result[$item->getId()][TierInterface::KEY_NAME] = $name;
            $result[$item->getId()][TierInterface::KEY_DESCRIPTION] = $description;
            $result[$item->getId()][TierInterface::KEY_TEMPLATE_ID] = $templateId;
            $result[$item->getId()][TierInterface::KEY_TIER_LOGO] = $logo;
            if (!empty($result[$item->getId()][TierInterface::KEY_TIER_LOGO])) {
                $logoFile = $result[$item->getId()][TierInterface::KEY_TIER_LOGO];
                $logoPath = $this->fileProcessor->getAbsoluteMediaPath();
                $result[$item->getId()][TierInterface::KEY_TIER_LOGO] = [
                    [
                        'name' => $logoFile,
                        'file' => $logoFile,
                        'url'  => $this->fileProcessor->getLogoMediaUrl($logoFile),
                        'size' => $this->fileProcessor->getFileSize($logoPath . DIRECTORY_SEPARATOR . $logoFile),
                        'type' => $this->fileProcessor->getFileMimeType($logoPath . DIRECTORY_SEPARATOR . $logoFile),
                    ]
                ];
            }
        }

        return $result;
    }
}
