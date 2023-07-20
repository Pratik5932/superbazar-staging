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



namespace Mirasvit\RewardsAdminUi\Ui\Spending\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Mirasvit\Rewards\Model\ResourceModel\Spending\Rule\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    private $url;
    private $pool;
    private $request;
    private $registry;

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
        $this->url = $url;
        $this->pool = $pool;
        $this->registry = $registry;
        $this->request = $request;
        $this->collection = $collectionFactory->create()
            ->addWebsiteColumn()
            ->addSpendPointsColumn()
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
        /** @var \Mirasvit\Rewards\Model\Spending\Rule $spendingRule */
        $spendingRule = $this->registry->registry('current_spending_rule');
        if ($spendingRule) {
            $item['front_name'] = $spendingRule->getFrontName();
        }

        return $item;
    }
}
