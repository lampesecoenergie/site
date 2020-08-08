<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 3/1/18
 * Time: 3:30 PM
 */

namespace Ced\Cdiscount\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Tax extends AbstractSource
{

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => 'tax 6',
                'label' => 'tax 6'
            ],
            [
                'value' => 'default',
                'label' => 'default'
            ]
        ];
    }
}
