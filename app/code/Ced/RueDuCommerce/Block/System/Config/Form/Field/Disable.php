<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 29/12/17
 * Time: 8:03 PM
 */

namespace Ced\RueDuCommerce\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;

class Disable extends Field
{
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setReadonly(true);
        return $element->getElementHtml();
    }
}
