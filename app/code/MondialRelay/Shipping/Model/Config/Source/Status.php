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

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 */
class Status implements OptionSourceInterface
{
    const SHIPMENT_STATUS_MONDIAL_RELAY_PENDING = 100;

    const SHIPMENT_STATUS_MONDIAL_RELAY_PROCESSING = 101;

    const SHIPMENT_STATUS_MONDIAL_RELAY_PREPARED = 102;

    const SHIPMENT_STATUS_MONDIAL_RELAY_SHIPPED = 103;

    const SHIPMENT_STATUS_MONDIAL_RELAY_ANOMALY = 104;

    /**
     * @var array
     */
    protected $options;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [
                [
                    'value' => self::SHIPMENT_STATUS_MONDIAL_RELAY_PENDING,
                    'label' => __('Pending'),
                ],
                [
                    'value' => self::SHIPMENT_STATUS_MONDIAL_RELAY_PROCESSING,
                    'label' => __('Processing'),
                ],
                [
                    'value' => self::SHIPMENT_STATUS_MONDIAL_RELAY_PREPARED,
                    'label' => __('Prepared'),
                ],
                [
                    'value' => self::SHIPMENT_STATUS_MONDIAL_RELAY_SHIPPED,
                    'label' => __('Shipped'),
                ],
                [
                    'value' => self::SHIPMENT_STATUS_MONDIAL_RELAY_ANOMALY,
                    'label' => __('Anomaly'),
                ]
            ];
        }
        return $this->options;
    }
}
