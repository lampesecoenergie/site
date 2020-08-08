<?php

namespace Ced\RueDuCommerce\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class WarrantyPeriod extends AbstractSource
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
                'value' => '1 Month',
                'label' => __('1 Month')
            ],
            [
                'value' => '2 Months',
                'label' => __('2 Months')
            ],
            [
                'value' => '3 Months',
                'label' => __('3 Months')
            ],
            [
                'value' => '4 Months',
                'label' => __('4 Months')
            ],
            [
                'value' => '5 Months',
                'label' => __('5 Months')
            ],
            [
                'value' => '6 Months',
                'label' => __('6 Months')
            ],

            [
                'value' => '7 Months',
                'label' => __('7 Months')
            ],
            [
                'value' => '8 Months',
                'label' => __('8 Months')
            ],
            [
                'value' => '9 Months',
                'label' => __('9 Months')
            ],
            [
                'value' => '10 Months',
                'label' => __('10 Months')
            ],
            [
                'value' => 'Life Time Warranty',
                'label' => __('Life Time Warranty')
            ],
            [
                'value' => '1 Year',
                'label' => __('1 Year')
            ],
            [
                'value' => '2 Years',
                'label' => __('2 Years')
            ],
            [
                'value' => '3 Years',
                'label' => __('3 Years')
            ],
            [
                'value' => '4 Years',
                'label' => __('4 Years')
            ],
            [
                'value' => '5 Years',
                'label' => __('5 Years')
            ],
            [
                'value' => '6 Years',
                'label' => __('6 Years')
            ],
            [
                'value' => '6 Years',
                'label' => __('6 Years')
            ],
            [
                'value' => '7 Years',
                'label' => __('7 Years')
            ],
            [
                'value' => '8 Years',
                'label' => __('8 Years')
            ],
            [
                'value' => '9 Years',
                'label' => __('9 Years')
            ],
            [
                'value' => '6 Years',
                'label' => __('6 Years')
            ],
            [
                'value' => '10 Years',
                'label' => __('10 Years')
            ],
            [
                'value' => '15 Years',
                'label' => __('15 Years')
            ],
            [
                'value' => '18 Years',
                'label' => __('18 Years')
            ],
            [
                'value' => '20 Years',
                'label' => __('20 Years')
            ],
            [
                'value' => '25 Years',
                'label' => __('25 Years')
            ],
            [
                'value' => '30 Years',
                'label' => __('30 Years')
            ],

        ];
    }
}
