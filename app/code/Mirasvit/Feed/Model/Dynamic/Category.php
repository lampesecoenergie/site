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



namespace Mirasvit\Feed\Model\Dynamic;

use Magento\Catalog\Model\CategoryFactory as CatalogCategoryFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

/**
 * @method string getName()
 * @method string getCode()
 */
class Category extends AbstractModel
{
    /**
     * @var array
     */
    protected $mapping;

    /**
     * @var array
     */
    protected $assocMapping;

    /**
     * @var CatalogCategoryFactory
     */
    protected $categoryFactory;

    /**
     * {@inheritdoc}
     * @param CatalogCategoryFactory $categoryFactory
     * @param Context                $context
     * @param Registry               $registry
     */
    public function __construct(
        CatalogCategoryFactory $categoryFactory,
        Context $context,
        Registry $registry
    ) {
        $this->categoryFactory = $categoryFactory;

        parent::__construct($context, $registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Feed\Model\ResourceModel\Dynamic\Category');
    }

    /**
     * Current mapping
     *
     * @return array
     */
    public function getMapping()
    {
        if ($this->mapping == null) {
            $this->buildMapping();
        }

        return $this->mapping;
    }

    /**
     * Current mapping as associative array
     *
     * @return array
     */
    public function getAssocMapping()
    {
        if ($this->assocMapping == null) {
            foreach ($this->getMapping() as $map) {
                $this->assocMapping[$map['category_id']] = $map;
            }
        }

        return $this->assocMapping;
    }

    /**
     * Build mapping
     *
     * @param int $parentId
     * @return void
     */
    protected function buildMapping($parentId = 0)
    {
        $userMapping = $this->getData('mapping');

        $collection = $this->categoryFactory->create()
            ->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('children_count')
            ->addAttributeToFilter('parent_id', $parentId)
            ->setOrder('position', 'asc')
            ->load();

        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($collection as $category) {
            $categoryId = $category->getId();

            if (isset($userMapping[$categoryId])) {
                $map = $userMapping[$categoryId];
            } else {
                $map = '';
            }

            if ($category->getName() || $category->getLevel() == 0) {
                $this->mapping[] = [
                    'category_id' => $categoryId,
                    'name'        => $category->getName() ? $category->getName() : __('Root Category')->__toString(),
                    'map'         => $map,
                    'level'       => $category->getLevel(),
                    'path'        => $category->getPath(),
                    'parent_id'   => $category->getParentId(),
                    'has_childs'  => $category->getChildrenCount() ? true : false,
                ];
            }

            if ($category->getChildrenCount()) {
                $this->buildMapping($category->getId());
            }
        }
    }

    /**
     * Return mapping value by category id
     *
     * @param int $categoryId
     * @return string
     */
    public function getMappingValue($categoryId)
    {
        $result = '';

        $mapping = $this->getAssocMapping();

        if (isset($mapping[$categoryId])) {
            $map = $mapping[$categoryId];

            if ($map['map'] != '') {
                $result = $map['map'];
            } else {
                $path = explode('/', $map['path']);
                $path = array_reverse($path);
                foreach ($path as $id) {
                    if (isset($mapping[$id])) {
                        $parentMap = $mapping[$id];

                        if ($parentMap['map'] != '') {

                            $result = $parentMap['map'];
                            break;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getRowsToExport()
    {
        $array = [
            'name',
            'type',
            'mapping_serialized',
            'mapping'
        ];

        return $array;
    }
}
