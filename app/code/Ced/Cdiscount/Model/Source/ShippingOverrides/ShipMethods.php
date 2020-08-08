<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 13/3/18
 * Time: 10:30 AM
 */

namespace Ced\Cdiscount\Model\Source\ShippingOverrides;

class ShipMethods implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'Standard',
                'value' => 'Standard'
            ],
            [
                'label' => 'Tracked',
                'value' => 'Tracked'
            ],
            [
                'label' => 'Registered',
                'value' => 'Registered'
            ],
            [
                'label' => 'Relay',
                'value' => 'Relay'
            ],
            [
                'label' => 'RelaisColis',
                'value' => 'RelaisColis'
            ],
            [
                'label' => 'SoColissimo',
                'value' => 'SoColissimo'
            ],
            [
                'label' => 'MondialRelay',
                'value' => 'MondialRelay'
            ],
            [
                'label' => 'BigParcelEco',
                'value' => 'BigParcelEco'
            ],
            [
                'label' => 'BigParcelStandard',
                'value' => 'BigParcelStandard'
            ],
            [
                'label' => 'BigParcelComfort',
                'value' => 'BigParcelComfort'
            ],
            [
                'label' => 'Express',
                'value' => 'Express'
            ],
            [
                'label' => 'Fast',
                'value' => 'Fast'
            ]

        ];
    }
}