<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 3/1/18
 * Time: 2:56 PM
 */

namespace Ced\Cdiscount\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class WarrantyType extends AbstractSource
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
                'value' => 'Local Manufacturer Warranty',
                'label' => __('Local Manufacturer Warranty')
            ],
            [
                'value' => 'International Manufacturer Warranty',
                'label' => __('International Manufacturer Warranty')
            ],
            [
                'value' => 'Local Supplier Warranty',
                'label' => __('Local Supplier Warranty')
            ],
            [
                'value' => 'No Warranty',
                'label' => __('No Warranty')
            ],
            [
                'value' => 'International Seller Warranty',
                'label' => __('International Seller Warranty')
            ]
        ];
    }
}
