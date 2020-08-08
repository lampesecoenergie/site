<?php

namespace Ced\RueDuCommerce\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class ReferenceType extends AbstractSource
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
                'value' => 'EAN',
                'label' => __('EAN')
            ],
            [
                'value' => 'ISBN',
                'label' => __('ISBN')
            ],
            [
                'value' => 'UPC',
                'label' => __('UPC')
            ],
            [
                'value' => 'MPN',
                'label' => __('MPN')
            ],
        

        ];
    }
}
