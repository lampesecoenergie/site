<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 3/1/18
 * Time: 3:30 PM
 */

namespace Ced\RueDuCommerce\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class LogisticClass extends AbstractSource
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
                'label' => 'Default Logistic Class'
            ],
            [
                'value' => 'FLAT',
                'label' => 'Flat Rate'
            ],
            [
                'value' => 'SLW',
                'label' => 'Small - Light Weight'
            ],
            [
                'value' => 'SMW',
                'label' => 'Small - Medium Weight'
            ],
            [
                'value' => 'SHW',
                'label' => 'Small - Heavy Weight'
            ],
            [
                'value' => 'MLW',
                'label' => 'Medium - Light Weight'
            ],
            [
                'value' => 'MMW',
                'label' => 'Medium - Medium Weight'
            ],
            [
                'value' => 'MHW',
                'label' => 'Medium - Heavy Weight'
            ],
            [
                'value' => 'MSHW',
                'label' => 'Medium - Super Heavy Weight'
            ],
            [
                'value' => 'LLW',
                'label' => 'Large - Light Weight'
            ],
            [
                'value' => 'LMW',
                'label' => 'Large - Medium Weight'
            ],
            [
                'value' => 'LHW',
                'label' => 'Large - Heavy Weight'
            ],
            [
                'value' => 'LSHW',
                'label' => 'Large - Super Heavy Weight'
            ],
            [
                'value' => 'L2MC',
                'label' => 'Large - 2 Men Carry'
            ],
            [
                'value' => 'OLW',
                'label' => 'Oversize - Light Weight'
            ],
            [
                'value' => 'OMW',
                'label' => 'Oversize - Medium Weight'
            ],
            [
                'value' => 'OHW',
                'label' => 'Oversize - Heavy Weight'
            ],
            [
                'value' => 'OSHW',
                'label' => 'Oversize - Super Heavy Weight'
            ],
            [
                'value' => 'SOA',
                'label' => 'Super Oversize - A'
            ],
            [
                'value' => 'O2MC',
                'label' => 'Oversize - 2 Men Carry'
            ],
            [
                'value' => 'SOB',
                'label' => 'Super Oversize - B'
            ],
            [
                'value' => 'FREE',
                'label' => 'FREE'
            ],
        ];
    }
}
