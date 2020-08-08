<?php

namespace Acyba\GLS\Model\Config\Source;

use \Magento\Framework\Option\ArrayInterface;

class LoadOnParent implements ArrayInterface
{
    /*
      * Option getter
      * @return array
    */
    public function toOptionArray()
    {
        $options = [
            ['value' => '0', 'label' => __('Self')],
            ['value' => '1', 'label' => __('Parent')]
        ];
        return $options;
    }
}
