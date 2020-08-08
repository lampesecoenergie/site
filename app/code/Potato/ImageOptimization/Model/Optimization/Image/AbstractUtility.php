<?php

namespace Potato\ImageOptimization\Model\Optimization\Image;

use Potato\ImageOptimization\Model\Config;
use Potato\ImageOptimization\Api\UtilityInterface;

abstract class AbstractUtility implements UtilityInterface
{
    const DEFAULT_TOOLS_PATH = '/app/code/Potato/ImageOptimization/tools';

    /** @var Config  */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param string $libName
     * @return string
     */
    protected function getLibPath($libName)
    {
        $customLibPath = $this->config->getCustomToolsPath();
        if (null == $customLibPath) {
            $customLibPath = self::DEFAULT_TOOLS_PATH;
        }
        if (0 !== strpos(BP, $customLibPath)) {
            //base path not found - add it
            $customLibPath = BP . DIRECTORY_SEPARATOR . ltrim($customLibPath, DIRECTORY_SEPARATOR);
        }
        $customLibFullPath = rtrim($customLibPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $libName;
        if (!file_exists($customLibFullPath)) {
            return $libName;
        }
        return $customLibFullPath;
    }
}
