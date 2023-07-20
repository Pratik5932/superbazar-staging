<?php
namespace Mexbs\AdditionalPromotions\Model;

class ImageUploader
{
    private $coreFileStorageDatabase;
    private $mediaDirectory;
    private $uploaderFactory;
    private $storeManager;
    private $logger;
    private $baseTmpPath;
    private $basePath;
    private $allowedFileExtensions;

    public function __construct(
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        $baseTmpPath,
        $basePath,
        $allowedFileExtensions
    ) {
        $this->coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->uploaderFactory = $uploaderFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->baseTmpPath = $baseTmpPath;
        $this->basePath = $basePath;
        $this->allowedFileExtensions = $allowedFileExtensions;
    }

    public function setBaseTmpPath($baseTmpPath)
    {
        $this->baseTmpPath = $baseTmpPath;
    }

    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    public function setAllowedExtensions($allowedFileExtensions)
    {
        $this->allowedFileExtensions = $allowedFileExtensions;
    }

    public function getBaseTmpPath()
    {
        return $this->baseTmpPath;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function getAllowedExtensions()
    {
        return $this->allowedFileExtensions;
    }

    public function getFilePath($path, $uploadedImageName)
    {
        return rtrim($path, '/') . '/' . ltrim($uploadedImageName, '/');
    }

    public function moveFileFromTmpDirectory($uploadedImageName)
    {
        $baseTemporaryPath = $this->getBaseTmpPath();
        $basePath = $this->getBasePath();

        $baseImagePath = $this->getFilePath($basePath, $uploadedImageName);
        $baseTmpImagePath = $this->getFilePath($baseTemporaryPath, $uploadedImageName);

        try {
            $this->coreFileStorageDatabase->copyFile(
                $baseTmpImagePath,
                $baseImagePath
            );
            $this->mediaDirectory->renameFile(
                $baseTmpImagePath,
                $baseImagePath
            );
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while saving the file(s).')
            );
        }

        return $uploadedImageName;
    }

    public function saveFileToTmpDirectory($fileId)
    {
        $baseTemporaryPath = $this->getBaseTmpPath();

        $fileUploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $fileUploader->setAllowedExtensions($this->getAllowedExtensions());
        $fileUploader->setAllowRenameFiles(true);

        $uploadResult = $fileUploader->save($this->mediaDirectory->getAbsolutePath($baseTemporaryPath));

        if (!$uploadResult) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('File can not be saved to the destination folder.')
            );
        }

        $uploadResult['tmp_name'] = str_replace('\\', '/', $uploadResult['tmp_name']);
        $uploadResult['path'] = str_replace('\\', '/', $uploadResult['path']);
        $uploadResult['url'] = $this->storeManager
                ->getStore()
                ->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . $this->getFilePath($baseTemporaryPath, $uploadResult['file']);
        $uploadResult['name'] = $uploadResult['file'];

        if (isset($uploadResult['file'])) {
            try {
                $relativePath = rtrim($baseTemporaryPath, '/') . '/' . ltrim($uploadResult['file'], '/');
                $this->coreFileStorageDatabase->saveFile($relativePath);
            } catch (\Exception $e) {
                $this->logger->critical($e);
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while saving the file(s).')
                );
            }
        }

        return $uploadResult;
    }
}
