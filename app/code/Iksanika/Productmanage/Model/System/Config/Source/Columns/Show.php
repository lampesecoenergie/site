<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace Iksanika\Productmanage\Model\System\Config\Source\Columns;

class Show implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $eavAttributeColletion,
        \Magento\Eav\Model\Entity $eavEntity
    ) {
        $this->context = $context;
        $this->eavAttributeCollection = $eavAttributeColletion;
        $this->eavEntity = $eavEntity;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
//        return [['value' => 1, 'label' => __('Yes')], ['value' => 0, 'label' => __('No')]];
        
        $columns = [
            [
                'value' => 'entity_id',   
                'label' => __('ID')
            ],
            [
                'value' => 'type_id',   
                'label' => __('Type (simple, bundle, etc)')
            ],
            [
                'value' => 'attribute_set_id',   
                'label' => __('Attribute Set')
            ],
            [
                'value' => 'qty',   
                'label' => __('Quantity')
            ],
            [
                'value' => 'is_in_stock',   
                'label' => __('Is in Stock')
            ],
            [
                'value' => 'websites',   
                'label' => __('Websites')
            ],
            [
                'value' => 'category_ids',   
                'label' => __('Category ID\'s')
            ],
            [
                'value' => 'category',   
                'label' => __('Categories')
            ],
            [
                'value' => 'related_ids',   
                'label' => __('Related: Relative Products IDs')
            ],
            [
                'value' => 'cross_sell_ids',   
                'label' => __('Related: Cross-Sell Products IDs')
            ],
            [
                'value' => 'up_sell_ids',   
                'label' => __('Related: Up-Sell Products IDs')
            ],
            [
                'value' => 'associated_groupped_ids',
                'label' => __('Associated IDs: for Groupped')
            ],
            [
                'value' => 'associated_configurable_ids',
                'label' => __('Associated IDs: for Configurable')
            ],
        ];
        
        $columnsCollection = $this->eavAttributeCollection->setEntityTypeFilter($this->eavEntity->setType('catalog_product')->getTypeId())->addFilter('is_visible', 1);
        
        foreach($columnsCollection->getItems() as $column) 
        {
            if($column->getAttributeCode() != 'quantity_and_stock_status')
            {
                $columns[] = [
                    'value' => $column->getAttributeCode(),   
                    'label' => $column->getFrontendLabel()
                ];
            }
        }
        
        return $columns;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $returnArray = [];
        foreach($this->toOptionArray() as $item)
        {
            $returnArray[$item['value']] = $item['label'];
        }
        return $returnArray;
    }
}
