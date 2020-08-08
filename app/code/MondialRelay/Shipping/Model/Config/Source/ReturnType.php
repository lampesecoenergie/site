<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2019 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ReturnType
 */
class ReturnType implements ArrayInterface
{
    const MONDIAL_RELAY_RETURN_TYPE_ADDRESS = 1;

    const MONDIAL_RELAY_RETURN_TYPE_RELAY = 2;

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
            self::MONDIAL_RELAY_RETURN_TYPE_ADDRESS => __('Address (LCC)'),
            self::MONDIAL_RELAY_RETURN_TYPE_RELAY   => __('Pickup location'),
        ];
    }
}
