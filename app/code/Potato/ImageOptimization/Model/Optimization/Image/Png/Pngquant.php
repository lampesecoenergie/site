<?php

namespace Potato\ImageOptimization\Model\Optimization\Image\Png;

use Potato\ImageOptimization\Model\Image;
use Potato\ImageOptimization\Model\Source\Image\Status as StatusSource;
use Potato\ImageOptimization\Model\Optimization\Image\AbstractUtility;
use Potato\ImageOptimization\Model\Source\Optimization\Error as ErrorSource;

class Pngquant extends AbstractUtility
{
    const LIB_PATH = 'pngquant';
    const DEFAULT_OPTIONS = '--quality=80-100';

    /**
     * @param string $imagePath
     * @return void
     * @throws \Exception
     */
    public function optimize($imagePath)
    {
        $command = $this->getLibPath(self::LIB_PATH)
            . ' ' . self::DEFAULT_OPTIONS
            . ' -f -o ' . escapeshellarg($imagePath)
            . ' ' . escapeshellarg($imagePath)
            . ' 2>&1'
        ;
        exec(
            $command,
            $result, $status
        );

        if ($status != 0 && ($status != 98 && $status != 99)) {
            $resultAsString = join("\n", $result);
            throw new \Exception(
                'Status ' . $status . ' : ' . $resultAsString . '. Current script owner: '
                . get_current_user() . '. Command:' . $command, ErrorSource::APPLICATION);
        }
    }

    /**
     * @return array
     */
    public function isAvailable()
    {
        $command = $this->getLibPath(self::LIB_PATH) . ' -V 2>&1';
        exec(
            $command,
            $result,
            $error
        );
        $result = true;
        if ($error != 0) {
            $result = false;
        }
        return [self::LIB_PATH => $result];
    }
}
