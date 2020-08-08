<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Availability
 */
class Availability extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (!$item['shipment_id']) {
                    $item[$this->getData('name')] = '<div class="grid-severity-major">' . __('Waiting for Shipment') . '</div>';
                } elseif (!$item['has_label']) {
                    $item[$this->getData('name')] = '<div class="grid-severity-minor">' . __('Waiting for Label') . '</div>';
                } else {
                    $item[$this->getData('name')] = '<div class="grid-severity-notice">' . __('Ready') . '</div>';
                }
            }
        }

        return $dataSource;
    }
}
