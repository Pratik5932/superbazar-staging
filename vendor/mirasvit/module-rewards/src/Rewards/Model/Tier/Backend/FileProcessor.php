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



namespace Mirasvit\Rewards\Model\Tier\Backend;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Theme\Model\Design\BackendModelFactory;
use Magento\Theme\Model\Design\Config\MetadataProvider;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class FileProcessor
{
    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var BackendModelFactory
     */
    protected $backendModelFactory;

    /**
     * @var Logo
     */
    protected $backendModel;

    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    const FILE_DIR = 'rewards/tier/logo';

    public function __construct(
        UploaderFactory $uploaderFactory,
        BackendModelFactory $backendModelFactory,
        Logo $backendModel,
        MetadataProvider $metadataProvider,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        $this->uploaderFactory     = $uploaderFactory;
        $this->backendModelFactory = $backendModelFactory;
        $this->backendModel        = $backendModel;
        $this->metadataProvider    = $metadataProvider;
        $this->storeManager        = $storeManager;
        $this->mediaDirectory      = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Save file to temp media directory
     *
     * @param string $fileId
     *
     * @return array
     * @throws LocalizedException
     */
    public function saveToTmp($fileId)
    {
        try {
            $result        = $this->save($fileId, $this->getAbsoluteTmpMediaPath());
            $result['url'] = $this->getTmpMediaUrl($result['file']);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $result;
    }

    /**
     * Save file to temp media directory
     *
     * @param array $fileData
     *
     * @return array
     * @throws LocalizedException
     */
    public function moveTmpToFile($fileData)
    {
        try {
            if (class_exists('\Magento\AwsS3\Driver\AwsS3', false) &&
                $this->mediaDirectory->getDriver() instanceof \Magento\AwsS3\Driver\AwsS3
            ) {
                $destinationUrl = $this->getAbsoluteMediaPath() . DIRECTORY_SEPARATOR . $fileData['file'];

                if (
                $this->mediaDirectory->getDriver()->rename(
                    $this->getAbsoluteTmpMediaPath() . DIRECTORY_SEPARATOR . $fileData['file'],
                    $destinationUrl
                    )
                ) {
                    $result = $fileData;

                    $result['url']  = $destinationUrl;
                    $result['path'] = $this->getAbsoluteMediaPath();
                } else {
                    $result = ['error' => 'Failed moving file', 'errorcode' => -1];
                }
            } else {
                $fileData['tmp_name'] = $this->getAbsoluteTmpMediaPath() . DIRECTORY_SEPARATOR . $fileData['file'];

                $result        = $this->save($fileData, $this->getAbsoluteMediaPath());
                $result['url'] = $this->getLogoMediaUrl($result['file']);
            }
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $result;
    }

    public function getFileSize(string $file): int
    {
        $driver = $this->mediaDirectory->getDriver();
        if (method_exists($driver, 'getMetadata')) {
            $metadata = $driver->getMetadata($file);

            return $metadata['size'];
        } else {
            return filesize($file);
        }
    }

    public function getFileMimeType(string $file): string
    {
        $driver = $this->mediaDirectory->getDriver();

        if (method_exists($driver, 'getMetadata')) {
            $metadata = $driver->getMetadata($file);

            return $metadata['mimetype'];
        } else {
            return mime_content_type($file);
        }
    }

    /**
     * Retrieve temp media url
     *
     * @param string $file
     *
     * @return string
     */
    protected function getTmpMediaUrl($file)
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . 'tmp/' . self::FILE_DIR . '/' . $this->prepareFile($file);
    }

    /**
     * Retrieve logo media url
     *
     * @param string $file
     *
     * @return string
     */
    public function getLogoMediaUrl($file)
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . self::FILE_DIR . '/' . $this->prepareFile($file);
    }

    /**
     * Prepare file
     *
     * @param string $file
     *
     * @return string
     */
    protected function prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }

    /**
     * Retrieve absolute temp media path
     * @return string
     */
    protected function getAbsoluteTmpMediaPath()
    {
        return $this->mediaDirectory->getAbsolutePath('tmp/' . self::FILE_DIR);
    }

    /**
     * Retrieve absolute media path
     * @return string
     */
    public function getAbsoluteMediaPath()
    {
        return $this->mediaDirectory->getAbsolutePath(self::FILE_DIR);
    }

    /**
     * Save image
     *
     * @param array|string $fileId
     * @param string       $destination
     *
     * @return array
     * @throws LocalizedException
     */
    protected function save($fileId, $destination)
    {
        /** @var \Magento\MediaStorage\Model\File\Uploader $uploader */
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $uploader->setAllowedExtensions($this->getAllowedExtensions());
        $uploader->addValidateCallback('size', $this->backendModel, 'validateMaxSize');

        $result = $uploader->save($destination);
        unset($result['path']);

        return $result;
    }

    /**
     * Getter for allowed extensions of uploaded files
     * @return string[]
     */
    public function getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png'];
    }
}
