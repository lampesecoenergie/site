<?php
namespace Potato\ImageOptimization\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem as FrameworkFilesystem;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Potato\ImageOptimization\Model\Source\Optimization\Error as ErrorSource;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\UrlInterface;

class Filesystem
{
    const DEFAULT_BACKUP_FOLDER_NAME = 'po_image_optimization_original_images';
    const TEMP_FOLDER_NAME = 'po_image_optimization_temp_images';

    const DEFAULT_FOLDER_PERMISSION = 0775;
    const DEFAULT_FILE_PERMISSION = 0664;

    /** @var FrameworkFilesystem  */
    protected $filesystem;

    /** @var AssetRepository  */
    protected $assetRepo;

    /** @var ModuleList  */
    protected $moduleList;

    /** @var UrlInterface  */
    protected $url;

    /**
     * @param FrameworkFilesystem $filesystem
     * @param AssetRepository $assetRepo
     * @param ModuleList $moduleList
     * @param UrlInterface $url
     */
    public function __construct(
        FrameworkFilesystem $filesystem,
        AssetRepository $assetRepo,
        ModuleList $moduleList,
        UrlInterface $url
    ) {
        $this->filesystem = $filesystem;
        $this->assetRepo = $assetRepo;
        $this->moduleList = $moduleList;
        $this->url = $url;
    }

    /**
     * @param string $image
     * @return string
     */
    protected function getBackupImagePath($image)
    {
        $path = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $path .= self::DEFAULT_BACKUP_FOLDER_NAME . DIRECTORY_SEPARATOR
            . trim(str_replace(BP, '', $image), DIRECTORY_SEPARATOR);
        return $path;
    }

    /**
     * @param $image
     * @return bool
     * @throws \Exception
     */
    public function createBackup($image)
    {
        $path = str_replace(BP . DIRECTORY_SEPARATOR, '', $this->getBackupImagePath($image));
        $result = false;

        $rootPath = BP;
        if (is_readable($rootPath . DIRECTORY_SEPARATOR . $path)) {
            //backup exist and readable
            return true;
        }
        $pathTargets = explode(DIRECTORY_SEPARATOR, $path);
        foreach ($pathTargets as $key => $target) {
            $rootPath .= DIRECTORY_SEPARATOR . $target;
            if (file_exists($rootPath)) {
                continue;
            }
            if ($key === count($pathTargets) - 1) {
                $result = @copy($image, $rootPath);//skip E_WARNING
                if (FALSE === $result) {
                    throw new \Exception('Unable to copy file: ' . $image . ' to ' . $rootPath,
                        ErrorSource::BACKUP_CREATION);
                }
                @chmod($rootPath, self::DEFAULT_FILE_PERMISSION);
                break;
            }
            mkdir($rootPath, self::DEFAULT_FOLDER_PERMISSION, true);
        }
        return $result;
    }

    /**
     * @param string $imagePath
     * @return string
     */
    public function createTempFile($imagePath)
    {
        $target = $this->getTempFilePath($imagePath);
        copy($imagePath, $target);
        return $target;
    }

    /**
     * @param string $imagePath
     * @return bool
     */
    public function removeTempFile($imagePath)
    {
        return @unlink($imagePath);
    }

    /**
     * @param string $imagePath
     * @return string
     */
    protected function getTempFilePath($imagePath)
    {
        $path = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath()
            . self::TEMP_FOLDER_NAME . DIRECTORY_SEPARATOR;
        if (false === file_exists($path)) {
            mkdir($path, self::DEFAULT_FOLDER_PERMISSION, true);
        }
        return $path . md5($imagePath) . '.img_temp';
    }

    /**
     * @param string $imagePath
     * @return $this
     * @throws \Exception
     */
    public function restoreImage($imagePath)
    {
        $backupImg = $this->getBackupImagePath($imagePath);
        $result = false;
        if ($backupImg && is_readable($backupImg)) {
            $content = file_get_contents($backupImg);
            $result = file_put_contents($imagePath, $content);
        }
        if (!$result) {
            throw new \Exception(__("Can't restore the backup. Please check the permissions of file and folders."),
                ErrorSource::CANT_UPDATE);
        }
        return $this;
    }

    /**
     * @param string $filePath
     * @return bool|string
     */
    public function getOriginalPathFromStatic($filePath)
    {
        $pubStaticPath = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW)->getAbsolutePath();
        $filePath = str_replace($pubStaticPath, '', $filePath);
        $params = $this->parsePath($filePath);
        $file = $params['file'];
        unset($params['file']);
        $asset = $this->assetRepo->createAsset($file, $params);
        return $asset->getSourceFile();
    }

    /**
     * Parse path to identify parts needed for searching original file
     * see original method in Magento\Framework\App\StaticResource
     *
     * @param string $path
     * @throws \InvalidArgumentException
     * @return array
     */
    protected function parsePath($path)
    {
        $path = ltrim($path, '/');
        $parts = explode('/', $path, 6);
        if (count($parts) < 5 || mb_strpos($path, '..') !== false) {
            //Checking that path contains all required parts and is not above static folder.
            throw new \InvalidArgumentException("Requested path '$path' is wrong.");
        }

        $result = [];
        $result['area'] = $parts[0];
        $result['theme'] = $parts[1] . '/' . $parts[2];
        $result['locale'] = $parts[3];
        if (count($parts) >= 6 && $this->moduleList->has($parts[4])) {
            $result['module'] = $parts[4];
        } else {
            $result['module'] = '';
            if (isset($parts[5])) {
                $parts[5] = $parts[4] . '/' . $parts[5];
            } else {
                $parts[5] = $parts[4];
            }
        }
        $result['file'] = $parts[5];
        return $result;
    }

    /**
     * @param string $imageUrl
     * @return string
     */
    public function createImagePathFromUrl($imageUrl)
    {
        $secure = false;
        if (preg_match('/^https:\/\//', $imageUrl)) {
            $secure = true;
        }
        $staticContentBaseUrl = trim($this->url->getBaseUrl(['_type' => UrlInterface::URL_TYPE_STATIC, '_secure' => $secure]), '/') . '/';
        if (strpos($imageUrl, $staticContentBaseUrl) !== False) {
            return str_replace(
                $staticContentBaseUrl,
                $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW)->getAbsolutePath(),
                $imageUrl
            );
        }
        $mediaContentBaseUrl = trim($this->url->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA, '_secure' => $secure]), '/') . '/';
        if (strpos($imageUrl, $mediaContentBaseUrl) !== False) {
            return str_replace(
                $mediaContentBaseUrl,
                $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath(),
                $imageUrl
            );
        }
        $baseUrl = trim($this->url->getBaseUrl(['_type' => UrlInterface::URL_TYPE_WEB, '_secure' => $secure]), '/') . '/';
        return str_replace(
            $baseUrl,
            $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath(),
            $imageUrl
        );
    }
}
