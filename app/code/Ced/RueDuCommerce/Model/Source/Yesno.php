<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 3/1/18
 * Time: 3:30 PM
 */

namespace Ced\RueDuCommerce\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Yesno extends AbstractSource
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
                'value' => '',
                'label' => '--Please Select--'
            ],
            [
                'value' => '1',
                'label' => 'Yes'
            ],
            [
                'value' => '0',
                'label' => 'No'
            ]
        ];
    }
}
