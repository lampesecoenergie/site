<?php

namespace Potato\ImageOptimization\Manager;

use Potato\ImageOptimization\Model\ResourceModel\ImageRepository;
use Potato\ImageOptimization\Model\Image as ImageModel;
use Potato\ImageOptimization\Model\Config;
use Potato\ImageOptimization\Model\Optimization\Image\Fabric;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Potato\ImageOptimization\Model\Source\Image\Type as ImageTypeSource;
use Potato\ImageOptimization\Model\Filesystem;
use Potato\ImageOptimization\Model\Source\Optimization\Error as ErrorSource;
use Potato\ImageOptimization\Logger\Logger;

class Image
{
    const PRODUCT_IMAGE_CACHE_PATH = 'pub/media/catalog/product/cache';
    const STATIC_IMAGE_PATH = 'pub/static';

    /** @var Config  */
    protected $config;

    /** @var Filesystem  */
    protected $filesystem;

    /** @var ImageRepository  */
    protected $imageRepository;

    /** @var Fabric  */
    protected $imageFabric;

    /** @var Logger  */
    protected $logger;

    /** @var ImageTypeSource  */
    protected $imageTypeSource;

    /**
     * @param ImageRepository $imageRepository
     * @param Config $config
     * @param Filesystem $filesystem
     * @param Fabric $imageFabric
     * @param Logger $logger
     * @param ImageTypeSource $imageTypeSource
     */
    public function __construct(
        ImageRepository $imageRepository,
        Config $config,
        Filesystem $filesystem,
        Fabric $imageFabric,
        Logger $logger,
        ImageTypeSource $imageTypeSource
    ) {
        $this->imageRepository = $imageRepository;
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->imageFabric = $imageFabric;
        $this->logger = $logger;
        $this->imageTypeSource = $imageTypeSource;
    }

    /**
     * @param ImageModel $image
     * @return $this
     * @throws \Exception
     */
    public function optimizeImage(ImageModel &$image)
    {
        if (!$this->isAvailableForOptimization($image)) {
            $image->delete();
            return $this;
        }
        //image available for optimization - change status to "In progress"
        $image->setStatus(StatusSource::STATUS_IN_PROGRESS)->save();
        $imagePath = $image->getPath();
        $result = $this->backupImage($imagePath);
        if (false === $result) {
            throw new \Exception(
                __("Can't create a backup of images. Please check the permissions of files and folders."),
                ErrorSource::BACKUP_CREATION
            );
        }
        $imageType = $this->imageTypeSource->getImageType($imagePath);
        $optimizationWorker = $this->imageFabric->getOptimizationWorkerByType($imageType);
        if (null === $optimizationWorker) {
            //unsupported image - skip
            $image->delete();
            return $this;
        }

        $tempFilePath = $this->filesystem->createTempFile($imagePath);
        if (!file_exists($tempFilePath) || !is_readable($tempFilePath)) {
            throw new \Exception(
                __("Temp file can't be created or read. Please check the permissions of files and folders."),
                ErrorSource::TEMP_CREATION
            );
        }
        $optimizationWorker->optimize($tempFilePath);

        $originalFileSize = filesize($imagePath);
        $optimizedFileSize = filesize($tempFilePath);
        if ((FALSE !== $originalFileSize && FALSE !== $optimizedFileSize && $optimizedFileSize > $originalFileSize)) {
            //size after optimization > size before optimization
            $this->filesystem->removeTempFile($tempFilePath);
            $image->setResult(__("%1 bytes -> %1 bytes", $originalFileSize));
            return $this;
        }
        $result = copy($tempFilePath, $imagePath);
        $this->filesystem->removeTempFile($tempFilePath);
        if (!$result) {
            throw new \Exception(
                __("Can't update the file. Please check the file permissions."),
                ErrorSource::CANT_UPDATE
            );
        }
        $image->setResult(__("%1 bytes -> %2 bytes", $originalFileSize, $optimizedFileSize));
        return $this;
    }

    /**
     * @param ImageModel $image
     * @return bool
     * @throws \Exception
     */
    protected function isAvailableForOptimization(ImageModel &$image)
    {
        $imagePath = $image->getPath();
        if (!file_exists($imagePath)) {
            return false;
        }
        if (!is_readable($imagePath)) {
            throw new \Exception(
                __("Can't read the file. Please check the file permissions."),
                ErrorSource::IS_NOT_READABLE
            );
        }
        $excludeDirs = $this->config->getExcludeDirs();
        foreach ($excludeDirs as $excludeDir) {
            $localPath = substr_replace($imagePath, '', 0, strlen(BP));
            if (strpos($localPath, DIRECTORY_SEPARATOR . trim($excludeDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR) !== false
                && $image->getStatus() != StatusSource::STATUS_OPTIMIZED
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $imagePath
     * @return bool
     * @throws \Exception
     */
    public function backupImage($imagePath)
    {
        if (!$this->config->isBackupEnabled()) {
            return true;
        }
        if ($this->isProductCacheImage($imagePath) || $this->isStaticImage($imagePath)) {
            //no backup for static and product cache
            return true;
        }
        return $this->filesystem->createBackup($imagePath);
    }

    public function isProductCacheImage($imagePath)
    {
        return false !== strpos($imagePath, self::PRODUCT_IMAGE_CACHE_PATH);
    }

    public function isStaticImage($imagePath)
    {
        return false !== strpos($imagePath, self::STATIC_IMAGE_PATH);
    }
}