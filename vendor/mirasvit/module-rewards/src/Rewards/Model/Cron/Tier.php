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



namespace Mirasvit\Rewards\Model\Cron;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Mirasvit\Rewards\Api\Service\Customer\TierInterface;
use Mirasvit\Rewards\Service\MenuLink;

class Tier extends AbstractCron
{
    private $lastCustomerId = null;

    private $pageLimit      = 5000;

    private $pagesPerRun    = 40;

    private $customerCounterFile;

    private $customerCollectionFactory;

    private $customerTierService;

    private $menuLink;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CollectionFactory $customerCollectionFactory,
        Filesystem $filesystem,
        TierInterface $customerTierService,
        MenuLink $menuLink
    ) {
        parent::__construct($filesystem);
        $this->menuLink                  = $menuLink;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->customerTierService       = $customerTierService;
    }

    /**
     * @return void
     * @throws \Zend_Db_Statement_Exception
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function execute()
    {
        $page = 1;

        $this->readLastCustomerId();
        $data = $this->getCustomers($page);

        $lastCustomerId = 0;
        while ($data->rowCount()) {
            foreach ($data as $customer) {
                try {
                    if (!$customer['website_id']) {
                        throw new LocalizedException(__('Customer does not assign to website'));
                    }
                    $this->customerTierService->updateCustomerTier($customer['entity_id']);
                } catch (InputException $e) {
                    echo "Cant update customer with ID " . $customer['entity_id'] . ". Error: " . $e->getMessage() . "\n";
                } catch (LocalizedException $e) {
                    echo "Cant update customer with ID " . $customer['entity_id'] . ". Error: " . $e->getMessage() . "\n";
                }
                $lastCustomerId = $customer['entity_id'];
                unset($customer);
            }
            $page++;
            if ($page > $this->pagesPerRun) {
                $this->writeLastCustomerId($lastCustomerId);
                break;
            }

            $data = $this->getCustomers($page);
        }
    }

    /**
     * @param int $page
     *
     * @return \Zend_Db_Statement_Interface
     */
    private function getCustomers($page)
    {
        $customers = $this->customerCollectionFactory->create();
        $customers->getSelect()->limitPage($page, $this->pageLimit);
        $customers->getSelect()->where('entity_id > ' . $this->lastCustomerId);

        $allowedCustomerGroups = $this->menuLink->getCustomerGroups();

        if ($this->menuLink->isShowMenu() && !in_array('all',  $allowedCustomerGroups)) {
            $customers->getSelect()->where('group_id in (?)', $allowedCustomerGroups);
        }

        return $customers->getSelect()->query();
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function readLastCustomerId()
    {
        if ($this->lastCustomerId === null) {
            $this->createCustomerCounterFile();

            $this->lastCustomerId = (int)fread($this->customerCounterFile, 1000);
        }

        return $this->lastCustomerId;
    }

    /**
     * @param int $customerId
     *
     * @return $this
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function writeLastCustomerId($customerId)
    {
        $this->createCustomerCounterFile();

        ftruncate($this->customerCounterFile, 0);
        rewind($this->customerCounterFile);
        fwrite($this->customerCounterFile, "$customerId");

        return $this;
    }

    /**
     * @return resource|null
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function createCustomerCounterFile()
    {
        if ($this->customerCounterFile === null) {
            $varDir = $this->filesystem
                ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::TMP)
                ->getAbsolutePath();
            if (!file_exists($varDir)) {
                @mkdir($varDir, 0777, true);
            }
            $file = $varDir . '/rewards_last_customer_id.lock';

            if (is_file($file)) {
                $this->customerCounterFile = fopen($file, 'r+');
            } else {
                $this->customerCounterFile = fopen($file, 'w+');
            }
        }

        return $this->customerCounterFile;
    }
}
