<?php

namespace Potato\ImageOptimization\Manager;

use Potato\ImageOptimization\Model\ResourceModel\ImageRepository;
use Potato\ImageOptimization\Model\Image;
use Potato\ImageOptimization\Model\Config;
use Potato\ImageOptimization\Manager\Image as ImageManager;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Potato\ImageOptimization\Model\Filesystem;
use Potato\ImageOptimization\Model\Source\Optimization\Error as ErrorSource;
use Potato\ImageOptimization\Logger\Logger;
use Magento\Catalog\Model\Product\ImageFactory as ProductImageFactory;
use Magento\Catalog\Model\Product\Image as ProductImage;
use Magento\Framework\View\ConfigInterface as ViewConfig;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\App\Area;
use Magento\Framework\Locale\Resolver;

class Restore
{
    /** @var Config  */
    protected $config;

    /** @var Filesystem  */
    protected $filesystem;

    /** @var ImageRepository  */
    protected $imageRepository;

    /** @var ImageManager  */
    protected $imageManager;

    /** @var Logger  */
    protected $logger;

    /** @var ProductImageFactory  */
    protected $productImageFactory;

    /** @var ThemeCollection  */
    protected $themeCollection;

    /** @var ViewConfig  */
    protected $viewConfig;

    /**
     * @param ImageRepository $imageRepository
     * @param Config $config
     * @param Filesystem $filesystem
     * @param ImageManager $imageManager
     * @param Logger $logger
     * @param ProductImageFactory $productImageFactory
     * @param ThemeCollection $themeCollection
     * @param ViewConfig $viewConfig
     */
    public function __construct(
        ImageRepository $imageRepository,
        Config $config,
        Filesystem $filesystem,
        ImageManager $imageManager,
        Logger $logger,
        ProductImageFactory $productImageFactory,
        ThemeCollection $themeCollection,
        ViewConfig $viewConfig
    ) {
        $this->imageRepository = $imageRepository;
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->imageManager = $imageManager;
        $this->logger = $logger;
        $this->productImageFactory = $productImageFactory;
        $this->themeCollection = $themeCollection;
        $this->viewConfig = $viewConfig;
    }

    /**
     * @param Image $image
     * @return $this
     * @throws \Exception
     */
    public function restoreImage(Image $image)
    {
        $imagePath = $image->getPath();
        if ($this->imageManager->isProductCacheImage($imagePath)) {
            $this->restoreCacheImage($imagePath);
        } elseif ($this->imageManager->isStaticImage($imagePath)) {
            $this->restoreStaticImage($imagePath);
        } else {
            $this->filesystem->restoreImage($imagePath);
        }

        $image
            ->setStatus(StatusSource::STATUS_PENDING)
            ->setResult(__("The image has been restored"))
            ->setTime(filemtime($image->getPath()))
            ->save();
        return $this;
    }

    /**
     * @param string $imagePath
     * @return $this
     * @throws \Exception
     */
    private function restoreCacheImage($imagePath)
    {
        $themes = $this->themeCollection->loadRegisteredThemes();
        $viewImages = $this->getViewImages($themes->getItems());
        $result = false;
        foreach ($viewImages as $viewImage) {
            $restoreImage = $this->makeImage($imagePath, $viewImage);
            $restoreImagePath = $this->filesystem->createImagePathFromUrl($restoreImage->getUrl());

            if ($restoreImagePath !=  $imagePath) {
                continue;
            }
            $restoreImage->resize();
            $restoreImage->saveFile();
            $result = true;
            break;
        }
        if (!$result) {
            throw new \Exception(__("Can't restore the backup. Please check the permissions of file and folders."),
                ErrorSource::CANT_UPDATE);
        }
        return $this;
    }

    /**
     * @param string $imagePath
     * @return $this
     * @throws \Exception
     */
    private function restoreStaticImage($imagePath)
    {
        $originalImagePath = $this->filesystem->getOriginalPathFromStatic($imagePath);
        copy($originalImagePath, $imagePath);
        return $this;
    }

    /**
     * @param string $imagePath
     * @param array $imageParams
     * @return ProductImage
     * @throws \Exception
     */
    private function makeImage($imagePath, array $imageParams)
    {
        /** @var ProductImage $image */
        $image = $this->productImageFactory->create();

        if (isset($imageParams['height'])) {
            $image->setHeight($imageParams['height']);
        }
        if (isset($imageParams['width'])) {
            $image->setWidth($imageParams['width']);
        }
        if (isset($imageParams['aspect_ratio'])) {
            $image->setKeepAspectRatio($imageParams['aspect_ratio']);
        }
        if (isset($imageParams['frame'])) {
            $image->setKeepFrame($imageParams['frame']);
        }
        if (isset($imageParams['transparency'])) {
            $image->setKeepTransparency($imageParams['transparency']);
        }
        if (isset($imageParams['constrain'])) {
            $image->setConstrainOnly($imageParams['constrain']);
        }
        if (isset($imageParams['background'])) {
            $image->setBackgroundColor($imageParams['background']);
        }

        $imagePathPart = explode('/', $imagePath);
        if ($cacheKey = array_search('cache', $imagePathPart)) {
            $imagePathPart = array_slice($imagePathPart, $cacheKey + 2);
        }
        $originalImagePath = implode('/', $imagePathPart);
        $image->setDestinationSubdir($imageParams['type']);
        $image->setBaseFile($originalImagePath);
        return $image;
    }

    /**
     * Get view images data from themes
     * @param array $themes
     * @return array
     */
    private function getViewImages(array $themes)
    {
        $viewImages = [];
        foreach ($themes as $theme) {
            $config = $this->viewConfig->getViewConfig([
                'area' => Area::AREA_FRONTEND,
                'themeModel' => $theme,
                'locale' => Resolver::DEFAULT_LOCALE
            ]);
            $images = $config->getMediaEntities('Magento_Catalog', ImageHelper::MEDIA_TYPE_CONFIG_NODE);
            foreach ($images as $imageId => $imageData) {
                $uniqIndex = $this->getUniqueImageIndex($imageData);
                $imageData['id'] = $imageId;
                $viewImages[$uniqIndex] = $imageData;
            }
        }
        return $viewImages;
    }

    /**
     * Get unique image index
     * @param array $imageData
     * @return string
     */
    private function getUniqueImageIndex(array $imageData)
    {
        ksort($imageData);
        unset($imageData['type']);
        return md5(json_encode($imageData));
    }
}