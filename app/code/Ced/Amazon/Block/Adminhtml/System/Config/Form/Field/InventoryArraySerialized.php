<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 27/7/19
 * Time: 1:36 PM
 */

namespace Ced\Amazon\Block\Adminhtml\System\Config\Form\Field;

class InventoryArraySerialized extends \Magento\Config\Model\Config\Backend\Serialized
{
    public function beforeSave()
    {
        $value = $this->getValue();

        if (is_array($value)) {
            $value = $this->unique($value, 'account');
        }

        $this->setValue($value);
        return parent::beforeSave();
    }

    function unique($array, $key1)
    {
        $parsedArray = [];
        $i = 0;
        $keyArray = [];
        foreach ($array as $key => $val) {
            if (!isset($val[$key1])) {
                continue;
            }

            if (!in_array($val[$key1], $keyArray)) {
                $keyArray[$i] = $val[$key1];
                $parsedArray[$key] = $val;
            }
            $i++;
        }
        return $parsedArray;
    }
}
