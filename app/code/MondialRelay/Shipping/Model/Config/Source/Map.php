<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2017 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Map
 */
class Map implements ArrayInterface
{
    /**
     * Get options as array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $options;
    }

    /**
     * Get options as array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'gmaps' => __('Google Maps'),
            'osm'   => __('OpenStreetMap (free)'),
        ];
    }
}
