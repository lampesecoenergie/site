<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 15/1/18
 * Time: 6:32 PM
 */

namespace Ced\RueDuCommerce\Model\Selection\Edit;


use Magento\Framework\Option\ArrayInterface;

class Options implements ArrayInterface
{

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [
            0 => [
                'label' => 'Enabled',
                'value' => 1
            ],
            1  => [
                'label' => 'Disabled',
                'value' => 0
            ]
        ];

        return $options;
    }
}