<?php

namespace Potato\ImageOptimization\Model\Source\Optimization;

use Magento\Framework\Data\OptionSourceInterface;

class Error implements OptionSourceInterface
{
    const IS_NOT_READABLE   = 1;
    const CANT_UPDATE     = 2;
    const STATIC_CANT_UPDATE = 3;
    const BACKUP_CREATION   = 4;
    const UNSUPPORTED_IMAGE  = 5;
    const APPLICATION   = 6;
    const TEMP_CREATION = 7;
    const PHP_WARNING = 7;

    /**
     * @return array
     */
    public function getOptionArray()
    {
        return [
            self::IS_NOT_READABLE => __("File is not readable"),
            self::CANT_UPDATE => __("File can't be updated"),
            self::STATIC_CANT_UPDATE => __("Static file can't be updated"),
            self::BACKUP_CREATION => __("Can't create a backup"),
            self::UNSUPPORTED_IMAGE => __("Unsupported image type"),
            self::APPLICATION => __("Application error"),
            self::TEMP_CREATION => __("Incorrect temp file"),
            self::PHP_WARNING => __("Run-time error")
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->getOptionArray();
        $result = [];
        foreach ($options as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }
        return $result;
    }

    /**
     * @param string $errorCode
     * @return string
     */
    public function getLabelByCode($errorCode)
    {
        $options = $this->getOptionArray();
        if (isset($options[$errorCode])) {
            return $options[$errorCode];
        }
        return '';
    }
}
