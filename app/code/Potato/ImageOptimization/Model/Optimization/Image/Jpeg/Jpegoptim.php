<?php

namespace Potato\ImageOptimization\Model\Optimization\Image\Jpeg;

use Potato\ImageOptimization\Model\Source\Optimization\Error as ErrorSource;
use Potato\ImageOptimization\Model\Optimization\Image\AbstractUtility;

class Jpegoptim extends AbstractUtility
{
    const LIB_PATH = 'jpegoptim';
    const DEFAULT_OPTIONS = '-f -o --strip-all --strip-icc --strip-iptc';
    const COMPRESSION_LEVEL = '-m85';

    /**
     * @param string $imagePath
     * @return void
     * @throws \Exception
     */
    public function optimize($imagePath)
    {
        $command = $this->getLibPath(self::LIB_PATH) . ' '
            . self::DEFAULT_OPTIONS . ' '
            . self::COMPRESSION_LEVEL . ' "' . $imagePath . '" 2>&1'
        ;
        exec(
            $command,
            $result,
            $error
        );
        $stringResult = implode(' ', $result);
        if (empty($result) || $error != 0) {
            throw new \Exception(__('Application for JP(E)G files optimization returns the error. Error code: %1 %2. Current script owner: %3. Command: %4',
                $error, $stringResult, get_current_user(), $command), ErrorSource::APPLICATION);
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
