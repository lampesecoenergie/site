<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Code
 */
class Code implements ArrayInterface
{
    const MONDIAL_RELAY_CODE_24R = '24R';

    const MONDIAL_RELAY_CODE_24L = '24L';

    const MONDIAL_RELAY_CODE_DRI = 'DRI';

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
            self::MONDIAL_RELAY_CODE_24R => __('Pickup %1 (Up to %2kgs, %3cm)', 'L', 30, 150),
            self::MONDIAL_RELAY_CODE_24L => __('Pickup %1 (Up to %2kgs, %3cm)', 'XL', 50, 200),
            self::MONDIAL_RELAY_CODE_DRI => __('Pickup %1 (Up to %2kgs, %3cm)', 'Drive', 150, 650),
        ];
    }
}
