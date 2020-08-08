<?php

namespace Potato\Crawler\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Protocol
 */
class Protocol implements OptionSourceInterface
{
    const HTTP_VALUE  = 'http';
    const HTTPS_VALUE = 'https';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::HTTP_VALUE, 'label' => __("HTTP")],
            ['value' => self::HTTPS_VALUE, 'label' => __("HTTPS")],
        ];
    }
}