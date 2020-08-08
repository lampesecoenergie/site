<?php

namespace Potato\ImageOptimization\Api;

interface UtilityInterface
{
    /**
     * @param string $imagePath
     * @return void
     */
    public function optimize($imagePath);
}
