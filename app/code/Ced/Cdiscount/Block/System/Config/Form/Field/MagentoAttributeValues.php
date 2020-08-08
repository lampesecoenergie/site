<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 16/3/18
 * Time: 11:37 AM
 */

namespace Ced\Cdiscount\Block\System\Config\Form\Field;

class MagentoAttributeValues implements \Magento\Framework\Option\ArrayInterface
{
    public $product;
    public $collectionFactory;
    public $options;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollection
    ) {
        $this->collectionFactory = $attributeCollection;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $magentoattributeCodeArray = [];
        $collection = $this->collectionFactory->create()->getItems();
        $magentoattributeCodeArray[] = ['label' => '----Please Select----', 'value' => 0];
        $allowedAttrsType = ['price', 'text'];
        foreach ($collection as $attribute) {
           if (in_array($attribute->getFrontendInput(), $allowedAttrsType) && !empty($attribute->getFrontendLabel())) {
               $magentoattributeCodeArray[] = [
                   'label' => $attribute->getFrontendLabel().'   ----> '.$attribute->getFrontendInput().' Type',
                   'value' => $attribute->getAttributecode()
               ];
           }
        }
        return $magentoattributeCodeArray;
    }
}
