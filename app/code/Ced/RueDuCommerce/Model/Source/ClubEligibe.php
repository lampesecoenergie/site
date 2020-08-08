<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 3/1/18
 * Time: 3:30 PM
 */

namespace Ced\RueDuCommerce\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class ClubEligibe extends AbstractSource
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
                'value' => 'true',
                'label' => 'Yes'
            ],
            [
                'value' => 'false',
                'label' => 'No'
            ]
        ];
    }
}
