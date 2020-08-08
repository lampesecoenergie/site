<?php

namespace Potato\ImageOptimization\Api;

interface OptimizationInterface
{
    /**
     * @param string $imagePath
     * @return $this
     */
    public function optimize($imagePath);
}
