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



namespace Mirasvit\RewardsAdminUi\Ui\Notification\Form;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Rewards\Model\ResourceModel\Notification\Rule\CollectionFactory;
use Magento\Framework\Registry;

class DataProvider extends AbstractDataProvider
{
    private $url;
    private $request;
    private $registry;

    /**
     * DataProvider constructor.
     * @param RequestInterface $request
     * @param UrlInterface $url
     * @param CollectionFactory $collectionFactory
     * @param Registry $registry
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $url,
        CollectionFactory $collectionFactory,
        Registry $registry,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->url = $url;
        $this->request = $request;
        $this->registry = $registry;
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
    public function getData()
    {
        $result = [];

        foreach ($this->collection as $item) {
            $result[$item->getId()] = $this->prepareItem($item->getData());
        }

        return $result;
    }

    /**
     * @param array $item
     * @return array
     */
    protected function prepareItem($item)
    {
        $notificationRule = $this->registry->registry('current_notification_rule');
        if ($notificationRule) {
            $item['message'] = $notificationRule->getMessage();
        }

        return $item;
    }
}
