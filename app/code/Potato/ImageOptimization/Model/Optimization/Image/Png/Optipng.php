<?php

namespace Potato\ImageOptimization\Model\Optimization\Image\Png;

use Potato\ImageOptimization\Model\Optimization\Image\AbstractUtility;
use Potato\ImageOptimization\Model\Source\Optimization\Error as ErrorSource;

class Optipng extends AbstractUtility
{
    const LIB_PATH = 'optipng';
    const DEFAULT_OPTIONS = '-o7 -clobber -strip all';

    /**
     * @param string $imagePath
     * @return void
     * @throws \Exception
     */
    public function optimize($imagePath)
    {
        $command = $this->getLibPath(self::LIB_PATH) . ' ' . self::DEFAULT_OPTIONS . ' "' . $imagePath . '" 2>&1';
        exec(
            $command,
            $result,
            $error
        );
        $stringResult = implode(' ', $result);

        if (empty($result) || ($error != 0 && strpos($stringResult, 'Warning') === false)) {
            throw new \Exception(__('Application for PNG files optimization returns the error. Error code: %1 %2. Current script owner: %3. Command: %4',
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
