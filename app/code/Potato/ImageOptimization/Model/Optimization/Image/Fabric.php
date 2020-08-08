<?php

namespace Potato\ImageOptimization\Model\Optimization\Image;

use Potato\ImageOptimization\Model\Source\Image\Type as ImageTypeSource;
use Magento\Framework\ObjectManagerInterface;

class Fabric
{
    /** @var ObjectManagerInterface  */
    protected $objectManager;

    /** @var ImageTypeSource  */
    protected $imageTypeSource;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ImageTypeSource $imageTypeSource
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ImageTypeSource $imageTypeSource
    ) {
        $this->objectManager = $objectManager;
        $this->imageTypeSource = $imageTypeSource;
    }

    /**
     * @param $imageType
     * @return null|mixed
     */
    public function getOptimizationWorkerByType($imageType)
    {
        $worker = null;
        $optimizationClass = $this->imageTypeSource->getClassByImageType($imageType);
        if ($optimizationClass) {
            $worker = $this->objectManager->create($optimizationClass);
        }
        return $worker;
    }
}