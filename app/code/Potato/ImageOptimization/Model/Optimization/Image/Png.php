<?php

namespace Potato\ImageOptimization\Model\Optimization\Image;

use Potato\ImageOptimization\Api\OptimizationInterface;

class Png implements OptimizationInterface
{
    const IMAGE_TYPE = 'image/png';
    
    /** @var Png\Optipng  */
    protected $pngManager;

    /**
     * @param Png\Optipng $pngManager
     */
    public function __construct(
        Png\Optipng $pngManager
    ) {
        $this->pngManager = $pngManager;
    }
    
    /**
     * @param string $imagePath
     * @return $this
     * @throws \Exception
     */
    public function optimize($imagePath)
    {
        $this->pngManager->optimize($imagePath);
        return $this;
    }

    /**
     * @return array
     */
    public function isLibAvailable()
    {
        return $this->pngManager->isAvailable();
    }
}
