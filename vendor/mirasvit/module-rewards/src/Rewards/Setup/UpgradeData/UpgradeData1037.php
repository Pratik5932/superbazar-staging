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


namespace Mirasvit\Rewards\Setup\UpgradeData;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Setup\Exception;

class UpgradeData1037 implements UpgradeDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->moveLogoToMediaFolder();
    }

    /**
     * move logo to media folder
     * @return void
     */
    private function moveLogoToMediaFolder()
    {
        try {
            $objManager = \Magento\Framework\App\ObjectManager::getInstance();
            $reader = $objManager->get('Magento\Framework\Module\Dir\Reader');
            $filesystem = $objManager->get('Magento\Framework\Filesystem');
            $type = \Magento\Framework\App\Filesystem\DirectoryList::MEDIA;
            $logoFilePath = $filesystem->getDirectoryRead($type)
                    ->getAbsolutePath().'mst_rewards/logo/default/';

            $modulePath = $reader->getModuleDir('', 'Mirasvit_RewardsCheckout');
            $mediaFile = $modulePath.'/view/frontend/web/images/logo.png';

            if (!file_exists($logoFilePath)) {
                mkdir($logoFilePath, 0777, true);
            }

            $filePath = $logoFilePath.'logo.png';

            if (!file_exists($filePath)) {
                if (file_exists($mediaFile)) {
                    copy($mediaFile, $filePath);
                }
            }

        } catch (\Exception $e) {
            throw new Exception('Unable to setup logo image');
        }
    }
}
