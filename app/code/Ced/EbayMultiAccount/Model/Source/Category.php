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
 * @package     Ced_EbayMultiAccount
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Model\Source;

/**
 * Class Category
 * @package Ced\EbayMultiAccount\Model\Source
 */
class Category extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Category constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * @return mixed
     */
    public function getAllOptions()
    {
        $options = $this->loadTree();
        foreach ($this->loadTree() as $category) {
            $options[$category['value']] = $category['label'];
        }

        return $options;
    }

    /**
     * @param \Magento\Framework\Data\Tree\Node $node
     * @param $values
     * @param int $level
     * @return mixed
     */
    public function buildCategoriesMultiselectValues(\Magento\Framework\Data\Tree\Node $node, $values, $level = 0)
    {
        $level++;

        $values[$node->getId()]['value'] = $node->getId();
        $values[$node->getId()]['label'] = str_repeat("--", $level) . $node->getName();

        foreach ($node->getChildren() as $child) {
            $values = $this->buildCategoriesMultiselectValues($child, $values, $level);
        }

        return $values;
    }

    /**
     * @return mixed
     */
    public function loadTree()
    {
        $store = 1;
        $parentId = /*$store ? Mage::app()->getStore($store)->getRootCategoryId() :*/
            1;  // Current store root category

        $tree = $this->_objectManager->create('\Magento\Catalog\Model\ResourceModel\Category\Tree')->load();

        $root = $tree->getNodeById($parentId);

        if ($root && $root->getId() == 1) {
            $root->setName(__('Root'));
        }

        $collection = $this->_objectManager->create('\Magento\Catalog\Model\Category')->getCollection()
            ->setStoreId($store)
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('is_active');

        $tree->addCollectionData($collection, true);

        return $this->buildCategoriesMultiselectValues($root, array());
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->getAllOptions() as $option) {
            $options[$option['value']] = (string)$option['label'];
        }
        return $options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {

        return $this->getOptions();
    }

    /**
     * @return array
     */
    public function getAllOption()
    {
        $options = $this->getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        return $options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * @param int|string $optionId
     * @return mixed|null
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

}
