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
 * Class Size
 */
class Size implements ArrayInterface
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
            'URL_PDF_A4'    => 'A4',
            'URL_PDF_A5'    => 'A5',
            'URL_PDF_10x15' => '10x15',
        ];
    }
}
