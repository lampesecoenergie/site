<?php

namespace Potato\ImageOptimization\Manager;

use Potato\ImageOptimization\Model\ResourceModel\ImageRepository;
use Potato\ImageOptimization\Model\Image;
use Potato\ImageOptimization\Model\Config;
use Potato\ImageOptimization\Model\Optimization\Image\Fabric;
use Potato\ImageOptimization\Manager\Image as ImageManager;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Potato\ImageOptimization\Model\Filesystem;
use Potato\ImageOptimization\Model\Source\Optimization\Error as ErrorSource;
use Potato\ImageOptimization\Logger\Logger;
use Potato\ImageOptimization\Model\Lock;
use Potato\ImageOptimization\Model\Source\Image\Type as ImageTypeSource;

class Optimization
{

    /** @var Config  */
    protected $config;

    /** @var Filesystem  */
    protected $filesystem;

    /** @var ImageRepository  */
    protected $imageRepository;

    /** @var Fabric  */
    protected $imageFabric;

    /** @var ImageManager  */
    protected $imageManager;

    /** @var Logger  */
    protected $logger;

    /** @var Lock  */
    protected $lock;

    /** @var ImageTypeSource  */
    protected $imageTypeSource;

    /**
     * @param ImageRepository $imageRepository
     * @param Config $config
     * @param Filesystem $filesystem
     * @param Fabric $imageFabric
     * @param \Potato\ImageOptimization\Manager\Image $imageManager
     * @param Logger $logger
     * @param Lock $lock
     * @param ImageTypeSource $imageTypeSource
     */
    public function __construct(
        ImageRepository $imageRepository,
        Config $config,
        Filesystem $filesystem,
        Fabric $imageFabric,
        ImageManager $imageManager,
        Logger $logger,
        Lock $lock,
        ImageTypeSource $imageTypeSource
    ) {
        $this->imageRepository = $imageRepository;
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->imageFabric = $imageFabric;
        $this->imageManager = $imageManager;
        $this->logger = $logger;
        $this->lock = $lock;
        $this->imageTypeSource = $imageTypeSource;
    }


    /**
     * @param [Image] $imageCollection
     * @return $this
     * @throws \Exception
     */
    public function optimizeImageCollection($imageCollection)
    {
        /** @var Image $image */
        foreach ($imageCollection as &$image) {
            if (!$this->lock->isCanRunProcess(Lock::OPTIMIZATION_LOCK_FILE)) {
                break;
            }
            $this->optimizeImage($image);

        }
        return $this;
    }

    /**
     * @param Image $image
     * @return $this
     * @throws \Exception
     */
    public function optimizeImage(Image $image)
    {
        try {
            $this->imageManager->optimizeImage($image);
            $image
                ->setStatus(StatusSource::STATUS_OPTIMIZED)
                ->setTime(filemtime($image->getPath()))
                ->save();
        } catch (\Exception $e) {
            $code = $e->getCode();
            if (!$code) {
                //E_WARNING
                $code = ErrorSource::PHP_WARNING;
            }
            $image
                ->setStatus(StatusSource::STATUS_ERROR)
                ->setErrorType($code)
                ->setResult($e->getMessage())
                ->save();
            $this->logger->error($e->getMessage());
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getOptimizationLibStatusList()
    {
        $availableImageTypeList = $this->imageTypeSource->getOptionArray();
        $statusList = [];
        foreach ($availableImageTypeList as $type => $class) {
            $worker = $this->imageFabric->getOptimizationWorkerByType($type);
            if (!$worker) {
                continue;
            }
            $statusList = array_merge($statusList, @$worker->isLibAvailable());
        }
        return $statusList;
    }
}