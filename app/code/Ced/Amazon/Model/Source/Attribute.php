<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory as GroupCollectionFactory;

class Attribute extends AbstractSource
{
    const DEFAULT_VALUE = "default_value";

    /** @var AttributeCollectionFactory  */
    public $attributeCollectionFactory;

    /** @var GroupCollectionFactory  */
    public $groupCollectionFactory;

    public function __construct(
        AttributeCollectionFactory $attributeCollectionFactory,
        GroupCollectionFactory $groupCollectionFactory
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    public function getAllOptions()
    {
        $options = [
            [
                'value' =>  self::DEFAULT_VALUE,
                'label' => __('Default Value [default_value]'),
            ]
        ];
        $attributeSetId = 4;
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection $groups */
        $groups = $this->groupCollectionFactory->create();
        $groups->setAttributeSetFilter($attributeSetId);

        /** @var \Magento\Eav\Model\Entity\Attribute\Group $group */
        foreach ($groups->getItems() as $group) {
            $id = $group->getData(\Magento\Eav\Model\Entity\Attribute\Group::GROUP_ID);
            $label = $group->getData(\Magento\Eav\Model\Entity\Attribute\Group::GROUP_NAME);
            $options[$id] = [
                'value' => [],
                'label' => __($label),
            ];

            /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributes */
            $attributes = $this->attributeCollectionFactory->create();
            $attributes->setAttributeGroupFilter($id);
            /** @var \Magento\Eav\Model\Attribute $attribute */
            foreach ($attributes->getItems() as $attribute) {
                $name = $attribute->getData('frontend_label');
                $code = $attribute->getAttributeCode();
                $options[$id]['value'][] = [
                    'value' => $code,
                    'leaf' => true,
                    'optgroup' => [],
                    'label' => __($name." [{$code}]"),
                ];
            }
        }

        ksort($options);

        return $options;
    }
}
