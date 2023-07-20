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



namespace Mirasvit\RewardsAdminUi\Ui\Earning\Form;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Mirasvit\Rewards\Model\ResourceModel\Earning\Rule\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    private $pool;
    private $registry;
    private $request;
    private $url;

    /**
     * DataProvider constructor.
     * @param CollectionFactory $collectionFactory
     * @param PoolInterface $pool
     * @param Registry $registry
     * @param RequestInterface $request
     * @param UrlInterface $url
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        PoolInterface $pool,
        Registry $registry,
        RequestInterface $request,
        UrlInterface $url,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->pool = $pool;
        $this->registry = $registry;
        $this->request = $request;
        $this->url = $url;
        $this->collection = $collectionFactory->create()
            ->addWebsiteColumn()
            ->addCustomerGroupColumn();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigData()
    {
        $config = parent::getConfigData();

        $config['submit_url'] = $this->url->getUrl(
            '*/*/save',
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
    public function getData()
    {
        $result = [];

        foreach ($this->collection as $item) {
            $result[$item->getId()] = $this->prepareItem($item->getData());
        }
        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $result = $modifier->modifyData($result);
        }

        return $result;
    }

    /**
     * @param array $item
     * @return array
     */
    protected function prepareItem($item)
    {
        $earningRule = $this->registry->registry('current_earning_rule');
        if ($earningRule) {
            $item['product_notification'] = $earningRule->getProductNotification();
            $item['front_name']           = $earningRule->getFrontName();
        }

        return $item;
    }
}
