<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Feed\Export\Resolver;

use Magento\Catalog\Model\Category;

class CategoryResolver extends AbstractResolver
{
    /**
     * Cache of loaded categories
     *
     * @var array
     */
    protected static $categories = [];

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [];
    }

    /**
     * Parent category model
     *
     * @param Category $category
     * @return Category
     */
    public function getParentCategory($category)
    {
        return $category->getParentCategory();
    }

    /**
     * Array of parent categories names
     *
     * @param Category $category
     * @return array
     */
    public function getPath($category)
    {
        $value = [];
        foreach ($category->getParentCategories() as $parent) {
            $value[$parent->getLevel()] = $parent->getName();
        }
        if (!count($value)) {
            $value[$category->getLevel()] = $category->getName();
        }

        ksort($value);

        return implode(" > ", $value);
    }

    /**
     * {@inheritdoc}
     */
    public function toString($value, $key = null)
    {
        if (is_object($value) && $value instanceof Category) {
            return $value->getName();
        }

        return parent::toString($value, $key);
    }

    /**
     * {@inheritdoc}
     *
     * @param Category $object
     * @param string $key
     * @param array $args
     *
     * @return string
     */
    public function resolve($object, $key, $args = [])
    {
        $category = $this->getCategory($object);
        $result = parent::resolve($category, $key, $args);

        if ($result === false) {
            $result = $category->getData($key);
        }

        return $result;
    }

    /**
     * Return category model
     *
     * @param Category $object
     *
     * @return Category
     */
    protected function getCategory($object)
    {
        if (!isset(self::$categories[$object->getId()])) {
            self::$categories[$object->getId()] = $object->load($object->getId());
        }

        return self::$categories[$object->getId()];
    }
}
