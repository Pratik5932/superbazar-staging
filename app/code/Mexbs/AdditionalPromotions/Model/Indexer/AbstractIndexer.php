<?php
namespace Mexbs\AdditionalPromotions\Model\Indexer;

use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Indexer\CacheContext;

abstract class AbstractIndexer implements IndexerActionInterface, MviewActionInterface, IdentityInterface
{
    protected $indexBuilder;
    protected $cacheContext;

    public function __construct(
        IndexBuilder $indexBuilder,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->indexBuilder = $indexBuilder;
    }

    public function execute($ids)
    {
        $this->executeList($ids);
    }

    public function executeFull()
    {
        $this->indexBuilder->reindexByProductIdsRuleIds();
    }

    public function executeList(array $ids)
    {
        if (!$ids) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Could not rebuild index for empty products array')
            );
        }
        $this->doExecuteList($ids);
    }

    abstract protected function doExecuteList($ids);

    public function executeRow($id)
    {
        if (!$id) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We can\'t rebuild the index for an undefined product.')
            );
        }
        $this->doExecuteRow($id);
    }

    abstract protected function doExecuteRow($id);

    protected function getCacheContext()
    {
        if (!($this->cacheContext instanceof CacheContext)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(CacheContext::class);
        } else {
            return $this->cacheContext;
        }
    }
}
