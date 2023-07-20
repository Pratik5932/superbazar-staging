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

use Magento\Framework\Filesystem;


abstract class AbstractCron
{
    /**
     * @var null
     */
    protected $_lockFile = null;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Filesystem $filesystem
    ) {
        $this->filesystem = $filesystem;
    }

    /**
     * @return void
     */
    public function run()
    {
        if (!$this->isLocked()) {
            $this->lock();

            $this->execute();

            $this->unlock();
        }
    }

    /**
     * @return void
     */
    abstract protected function execute();

    /**
     * @return bool
     */
    public function isLocked()
    {
        $fp = $this->_getLockFile();
        if (flock($fp, LOCK_EX | LOCK_NB)) {
            flock($fp, LOCK_UN);

            return false;
        }

        return true;
    }

    /**
     * @return object
     */
    public function lock()
    {
        flock($this->_getLockFile(), LOCK_EX | LOCK_NB);

        return $this;
    }

    /**
     * Разлочит файл.
     *
     * @return object
     */
    public function unlock()
    {
        flock($this->_getLockFile(), LOCK_UN);

        return $this;
    }

    /**
     * @return resource
     */
    protected function _getLockFile()
    {
        if ($this->_lockFile === null) {
            $varDir = $this->filesystem
                ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::TMP)
                ->getAbsolutePath();
            if (!file_exists($varDir)) {
                @mkdir($varDir, 0777, true);
            }
            $file = $varDir . '/rewards.lock';

            if (is_file($file)) {
                $this->_lockFile = fopen($file, 'w');
            } else {
                $this->_lockFile = fopen($file, 'x');
            }
            fwrite($this->_lockFile, date('r'));
        }

        return $this->_lockFile;
    }
}
