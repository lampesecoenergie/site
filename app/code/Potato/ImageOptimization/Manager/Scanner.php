<?php
namespace Potato\ImageOptimization\Manager;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Potato\ImageOptimization\Lib\FileFinder\FileFinder;
use Potato\ImageOptimization\Model\ResourceModel\ImageRepository;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Potato\ImageOptimization\Logger\Logger;
use Potato\ImageOptimization\Model\Config;
use Potato\ImageOptimization\Model\Image as ImageModel;
use Potato\ImageOptimization\Model\Source\Image\Type as ImageTypeSource;
use Potato\ImageOptimization\Model\Lock;

class Scanner
{
    const START_FILE_CACHE_ID = 'po_image_optimization_START_FILE_CACHE_ID';
    const SCAN_DATABASE_STEP = 500;
    const SCAN_DATABASE_STATUS_CACHE_KEY = 'po_imageoptimization_SCAN_DATABASE_STATUS';
    const CACHE_LIFETIME = 86400;

    /** @var CacheInterface  */
    protected $cache;

    /** @var Filesystem  */
    protected $filesystem;

    /** @var ImageRepository  */
    protected $imageRepository;

    /** @var Logger  */
    protected $logger;

    /** @var Config  */
    protected $config;

    /** @var Lock  */
    protected $lock;

    /** @var ImageTypeSource  */
    protected $imageTypeSource;
    
    protected $cachePostfix = null;
    
    protected $callbackCount = 0;

    protected $limit = null;

    protected $originalMaxNestingLevel = null;
    
    protected $callback = null;

    protected $excludeDirs = [];

    /**
     * @param ImageRepository $imageRepository
     * @param CacheInterface $cache
     * @param Logger $logger
     * @param Filesystem $filesystem
     * @param Config $config
     * @param ImageTypeSource $imageTypeSource
     * @param Lock $lock
     */
    public function __construct(
        ImageRepository $imageRepository,
        CacheInterface $cache,
        Logger $logger,
        Filesystem $filesystem,
        Config $config,
        ImageTypeSource $imageTypeSource,
        Lock $lock
    ) {
        $this->cache = $cache;
        $this->filesystem = $filesystem;
        $this->imageRepository = $imageRepository;
        $this->logger = $logger;
        $this->config = $config;
        $this->lock = $lock;
        $this->imageTypeSource = $imageTypeSource;
    }

    /**
     * @param null $limit
     * @return $this
     * @throws \Exception
     */
    public function saveImageGalleryFiles($limit = null)
    {
        $includeDirs = $this->getDirs();
        foreach ($includeDirs as $dir) {
            $this->prepareImagesFromDir(rtrim($dir, '/'), null, $limit);
            if (null !== $limit) {
                $limit -= $this->callbackCount;
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    protected function getDirs()
    {
        $includeDirs = $this->config->getIncludeDirs();
        $dirs = [];
        foreach ($includeDirs as $includeDir) {
            $fullPath = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath()
                . $includeDir;
            $fullPath = realpath($fullPath);
            if (!$fullPath) {
                continue;
            }
            $dirs[] = $fullPath;
        }
        if (!$dirs) {
            //if all included dirs invalid or deleted - run scanner for default static and media dirs
            $staticPath = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW)->getAbsolutePath();
            $mediaPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
            $dirs = [$staticPath, $mediaPath];
        }
        return $dirs;
    }

    /**
     * @param string $dirPath
     * @param string|null $startPath
     * @param int|null $limit
     * @return $this
     * @throws \Exception
     */
    public function prepareImagesFromDir($dirPath, $startPath = null, $limit = null)
    {
        $this->cachePostfix = md5($dirPath);
        if (null === $startPath && $this->cache->getFrontend()->test(self::START_FILE_CACHE_ID . $this->cachePostfix)) {
            $startPath = $this->cache->load(self::START_FILE_CACHE_ID . $this->cachePostfix);
        }
        $this->limit = $limit;
        $fileFinder = new FileFinder([
            'dir' => $dirPath,
            'callback' => array($this, 'saveFilePath'),
            'start_path' => $startPath,
            'exclude_dirs' => $this->config->getExcludeDirs()
        ]);
        $fileFinder->find();
        $this->cache->remove(self::START_FILE_CACHE_ID . $this->cachePostfix);
        return $this;
    }

    /**
     * @param string $filePath
     * @return bool
     */
    public function saveFilePath($filePath)
    {
        if (!$this->lock->isCanRunProcess(Lock::SCAN_LOCK_FILE)) {
            return false;
        }
        if (null !== $this->limit && $this->callbackCount >= $this->limit) {
            return false;
        }
        $this->cache->save($filePath, self::START_FILE_CACHE_ID . $this->cachePostfix, [], self::CACHE_LIFETIME);
        $result = null;
        if ($this->imageRepository->getByPath($filePath)->getId() || !$this->imageTypeSource->getImageType($filePath)) {
            return true;
        }

        $image = $this->imageRepository->create();
        $image
            ->setPath($filePath)
            ->setStatus(StatusSource::STATUS_PENDING)
        ;
        try {
            $image->save();
            $this->callbackCount++;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        if (null !== $this->callback) {
            call_user_func($this->callback, $this->callbackCount);
        }
        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function updateImagesFromDatabase()
    {
        $curPage = $this->cache->getFrontend()->test(self::SCAN_DATABASE_STATUS_CACHE_KEY) ? $this->cache->load(self::SCAN_DATABASE_STATUS_CACHE_KEY) : false;
        if (!$curPage) {
            $curPage = 0;
        }
        $previousImagesUpdated = $curPage * self::SCAN_DATABASE_STEP;
        $curPage++;
        $imageList = $this->imageRepository->getCollectionForScanDbPerPage($curPage);

        if ($curPage > $imageList->getLastPageNumber()) {
            $this->cache->remove(self::SCAN_DATABASE_STATUS_CACHE_KEY);
            return false;
        }
        $imageList->load();
        $imagesFound = count($imageList->getItems());

        /** @var ImageModel $item */
        foreach ($imageList->getItems() as $item) {
            if (!file_exists($item->getPath())) {
                $item->delete();
                continue;
            }
            if ((filemtime($item->getPath()) <= $item->getTime())
                || $item->getStatus() !== StatusSource::STATUS_OPTIMIZED
            ) {
                continue;
            }
            try {
                $item->setStatus(StatusSource::STATUS_OUTDATED);
                $item->save();
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
        if (null !== $this->callback) {
            call_user_func($this->callback, $previousImagesUpdated + $imagesFound);
        }
        $this->cache->save($curPage, self::SCAN_DATABASE_STATUS_CACHE_KEY, [], self::CACHE_LIFETIME);
        return true;
    }

    /**
     * @param $param
     * @return $this
     */
    public function setCallback($param)
    {
        $this->callback = $param;
        return $this;
    }
}
