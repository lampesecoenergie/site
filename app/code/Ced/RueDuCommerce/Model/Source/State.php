<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 3/1/18
 * Time: 3:30 PM
 */

namespace Ced\RueDuCommerce\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class State extends AbstractSource
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
                'value' => '11',
                'label' => 'New'
            ],
            [
                'value' => '10',
                'label' => 'Refurbished'
            ],
            /*[
                'value' => '7',
                'label' => 'Outlet - Refurbished'
            ],
            [
                'value' => '8',
                'label' => 'Outlet - New'
            ],
            [
                'value' => '6',
                'label' => 'Designer - Pre-Loved'
            ],*/

        ];
    }
}
