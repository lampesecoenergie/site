<?php

namespace Potato\ImageOptimization\Model\Optimization\Image;

use Potato\ImageOptimization\Api\OptimizationInterface;

class Jpeg implements OptimizationInterface
{
    const IMAGE_TYPE = 'image/jpeg';
    
    /** @var Jpeg\Jpegoptim  */
    protected $jpegManager;
    
    /**
     * @param Jpeg\Jpegoptim $jpegManager
     */
    public function __construct(
        Jpeg\Jpegoptim $jpegManager
    ) {
        $this->jpegManager = $jpegManager;
    }
    
    /**
     * @param string $imagePath
     * @return $this
     * @throws \Exception
     */
    public function optimize($imagePath)
    {
        $this->jpegManager->optimize($imagePath);
        return $this;
    }

    /**
     * @return array
     */
    public function isLibAvailable()
    {
        return $this->jpegManager->isAvailable();
    }
}
