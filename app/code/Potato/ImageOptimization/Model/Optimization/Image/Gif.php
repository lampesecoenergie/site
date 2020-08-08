<?php

namespace Potato\ImageOptimization\Model\Optimization\Image;

use Potato\ImageOptimization\Model\Source\Optimization\Error as ErrorSource;
use Potato\ImageOptimization\Api\OptimizationInterface;

class Gif implements OptimizationInterface
{
    const IMAGE_TYPE = 'image/gif';

    /** @var Png\Optipng */
    protected $pngManager;

    /** @var Gif\Gifsicle */
    protected $gifManager;

    /**
     * @param Png\Optipng $pngManager
     * @param Gif\Gifsicle $gifManager
     */
    public function __construct(
        Png\Optipng $pngManager,
        Gif\Gifsicle $gifManager
    ) {
        $this->pngManager = $pngManager;
        $this->gifManager = $gifManager;
    }

    /**
     * @param string $imagePath
     * @return $this
     * @throws \Exception
     */
    public function optimize($imagePath)
    {
        if (!$this->isAnimatedGif($imagePath)) {
            $pngFileName = dirname($imagePath)
                . DIRECTORY_SEPARATOR . basename($imagePath, ".gif") . '.png';
            if (file_exists($pngFileName)) {
                //after optimization img may be renamed to .png -> need do backup if same file already exists
                rename($pngFileName, $pngFileName . '_tmp');
            }
            $this->pngManager->optimize($imagePath);
            if (file_exists($pngFileName)) {
                rename($pngFileName, $imagePath);
            }
            if (file_exists($pngFileName . '_tmp')) {
                //restore previously renamed image
                rename($pngFileName . '_tmp', $pngFileName);
            }
            return $this;
        }
        $this->gifManager->optimize($imagePath);
        return $this;
    }

    /**
     * @param string $imagePath
     * @return bool
     * @throws \Exception
     */
    protected function isAnimatedGif($imagePath)
    {
        if (!is_readable($imagePath)) {
            throw new \Exception(__('The file is not readable'), ErrorSource::IS_NOT_READABLE);
        }
        $content = file_get_contents($imagePath);
        $strLoc = 0;
        $count = 0;

        // There is no point in continuing after we find a 2nd frame
        while ($count < 2) {
            $where1 = strpos($content, "\x00\x21\xF9\x04", $strLoc);
            if ($where1 === false) {
                break;
            }
            $str_loc = $where1 + 1;
            $where2 = strpos($content, "\x00\x2C", $str_loc);
            if ($where2 === false) {
                break;
            } else {
                if ($where1 + 8 == $where2) {
                    $count++;
                }
                $strLoc = $where2 + 1;
            }
        }
        // gif is animated when it has two or more frames
        return ($count >= 2);
    }

    /**
     * @return array
     */
    public function isLibAvailable()
    {
        $gif = $this->gifManager->isAvailable();
        $png = $this->pngManager->isAvailable();
        return array_merge($png, $gif);
    }
}
