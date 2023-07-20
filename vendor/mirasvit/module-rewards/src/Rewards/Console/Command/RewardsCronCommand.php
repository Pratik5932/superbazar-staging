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



namespace Mirasvit\Rewards\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;

/**
 * Emulates rewards cron execution
 */
class RewardsCronCommand extends Command
{
    private $objectManagerFactory;
    public function __construct(
        ObjectManagerFactory $objectManagerFactory
    ) {
        $this->objectManagerFactory = $objectManagerFactory;

        parent::__construct();
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setName('mirasvit:rewards:emulate:cron')
            ->setDescription('Emulates rewards cron execution');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $omParams = $_SERVER;
        $omParams[StoreManager::PARAM_RUN_CODE] = 'admin';
        $omParams[Store::CUSTOM_ENTRY_POINT_PARAM] = true;
        $objectManager = $this->objectManagerFactory->create($omParams);

        /** @var \Magento\Framework\App\State $state */
        $state = $objectManager->get('Magento\Framework\App\State');
        $state->setAreaCode('global');

        /** @var \Mirasvit\Rewards\Model\Cron $cronObserver */
        $cronObserver = $objectManager->create('Mirasvit\Rewards\Model\Cron');

        $cronObserver->run();
        $output->writeln('<info>' . 'Ran rewards jobs.' . '</info>');

        return 0;
    }
}
