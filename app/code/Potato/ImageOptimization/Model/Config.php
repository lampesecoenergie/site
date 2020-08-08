<?php
namespace Potato\ImageOptimization\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;


class Config
{
    const GENERAL_ENABLED = 'potato_image_optimization/general/is_enabled';
    const GENERAL_BACKUP_ENABLED = 'potato_image_optimization/general/image_backup';

    const INCLUDE_DIR_FOR_OPTIMIZATION = 'potato_image_optimization/advanced/include_dirs';
    const EXCLUDE_DIR_FROM_OPTIMIZATION = 'potato_image_optimization/advanced/exclude_dirs';

    const CUSTOM_PATH_TO_OPTIMIZATION_TOOLS = 'potato_image_optimization/advanced/custom_tools_path';

    const SCAN_RUNNING_CACHE_KEY = 'po_imageoptimization_SCAN_RUNNING';
    const OPTIMIZATION_RUNNING_CACHE_KEY = 'po_imageoptimization_OPTIMIZTION_RUNNING';

    /** @var ScopeConfigInterface  */
    protected $scopeConfig;

    /** @var mixed|null  */
    protected $serializer = null;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        if (@class_exists('\Magento\Framework\Serialize\Serializer\Json')) {
            $this->serializer = ObjectManager::getInstance()
                ->get('\Magento\Framework\Serialize\Serializer\Json');
        }
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->scopeConfig->getValue(self::GENERAL_ENABLED);
    }

    /**
     * @return bool
     */
    public function isBackupEnabled()
    {
        return (bool)$this->scopeConfig->getValue(self::GENERAL_BACKUP_ENABLED);
    }

    /**
     * @return array
     */
    public function getIncludeDirs()
    {
        $dirs = $this->scopeConfig->getValue(self::INCLUDE_DIR_FOR_OPTIMIZATION);
        if (!$dirs) {
            return [];
        }
        if ($this->serializer) {
            $dirs = $this->serializer->unserialize($dirs);
        } else {
            $dirs = unserialize($dirs);
        }
        $result = [];
        foreach ($dirs as $dir) {
            $result[] = $dir['folder'];
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getExcludeDirs()
    {
        $dirs = $this->scopeConfig->getValue(self::EXCLUDE_DIR_FROM_OPTIMIZATION);
        if (!$dirs) {
            return [];
        }
        if ($this->serializer) {
            $dirs = $this->serializer->unserialize($dirs);
        } else {
            $dirs = unserialize($dirs);
        }
        $result = [];
        foreach ($dirs as $dir) {
            $result[] = $dir['folder'];
        }
        return $result;
    }

    /**
     * @return string|null
     */
    public function getCustomToolsPath()
    {
        return $this->scopeConfig->getValue(self::CUSTOM_PATH_TO_OPTIMIZATION_TOOLS);
    }
}
