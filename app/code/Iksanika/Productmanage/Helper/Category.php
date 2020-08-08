<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iksanika\Productmanage\Helper;


/**
 * Catalog data helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Category extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    protected $categoryPath = array();

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Iksanika\Productmanage\Helper\Data $helper,
        \Magento\Catalog\Model\Category $category
    ) {
        parent::__construct($context);
        $this->_helper = $helper;
        $this->_category = $category;
    }
    
    public function getOptionsForFilter()
    {
        $parentCategory = $this->_category->load(($this->_helper->getStore()->getRootCategoryId() != 0 ? $this->_helper->getStore()->getRootCategoryId() : \Magento\Catalog\Model\Category::TREE_ROOT_ID));
        $this->generateCategoryPath($parentCategory);
//        die('no no no no no no no no no no no no ');
        $options    =   [0 => __('[NO CATEGORY]')];
        foreach($this->categoryPath as $i => $path)
        {
            $string = str_repeat(". ", max(0, ($path['level'] - 1) * 3)) . $path['name'];
            $options[$path['id']] = $string;
        }
        return $options;
    }
    
    public function generateCategoryPath($category)
    {
        if($category->getName())
        {
            $this->categoryPath[] = [
                'id'    => $category->getId(),
                'level' => $category->getLevel(),
                'name'  => $category->getName(),
            ];
        }
        if($category->hasChildren())
        {
            foreach($category->getChildrenCategories() as $child)
            {
                $this->generateCategoryPath($child);
            }
        }
    }
       
    /**
     * Genarate category structure with all categories
     *
     * @param int $rootId root category id
     * @return array sorted list category_id=>title
     */
    public function getTree($rootId)
    {
        $tree               =   array();
        $categoryCollection =   $this->_category->getCollection()->addNameToResult();
        
        $position = array();
        foreach ($categoryCollection as $category)
        {
            $path = explode('/', $category->getPath());
            if ((!$rootId || in_array($rootId, $path)) && $category->getLevel() && $category->getName())
            {
                $tree[$category->getId()] = array(
                    'label' => str_repeat('..', $category->getLevel()) . $category->getName() . ' ['.$category->getId().']',
                    'value' => $category->getId(),
                    'path'  => $path,
                );
            }
            $position[$category->getId()] = $category->getPosition();
        }
        
        foreach ($tree as $catId => $category)
        {
            $order = array();
            foreach ($category['path'] as $id)
            {
		$order[] = isset($position[$id]) ? $position[$id] : 0;
            }
            $tree[$catId]['order'] = $order;
        }
        
        usort($tree, array($this, 'compare'));
        
        return $tree;
    }
    
    /**
     * Compares category data
     *
     * @return int 0, 1 , or -1
     */
    public function compare($a, $b)
    {
        foreach ($a['path'] as $index => $id)
        {
            if (!isset($b['path'][$index]))
                return 1; // B path is shorther then A, and values before were equal
            if ($b['path'][$index] != $id)
                return ($a['order'][$index] < $b['order'][$index]) ? -1 : 1; // compare category positions at the same level
        }
        return ($a['value'] == $b['value']) ? 0 : -1; // B path is longer or equal then A, and values before were equal
    }      
    
}
