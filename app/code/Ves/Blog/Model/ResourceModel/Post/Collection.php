<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Blog
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Blog\Model\ResourceModel\Post;

use \Ves\Blog\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'post_id';

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->performAfterLoad('ves_blog_post_store', 'post_id');
        $this->getCategoriesAfterLoad();
        $this->getCommentsAfterLoad();
        $this->getTagsAfterLoad();
        return parent::_afterLoad();
    }


    /**
     * Perform operations after collection load
     *
     * @param string $tableName
     * @param string $columnName
     * @return void
     */
    protected function getCategoriesAfterLoad()
    {
        $items = $this->getColumnValues("post_id");
        if (count($items)) {
            $connection = $this->getConnection();
            $select = 'SELECT * FROM ' . $this->getTable('ves_blog_post_category');
            $categories = $connection->fetchAll($select);
            foreach ($this as $item) {
                $tmp = [];
                foreach ($categories as $k => $v) {
                    if($v['post_id'] == $item->getData("post_id")){
                        $select = 'SELECT * FROM ' . $this->getTable('ves_blog_category') . ' WHERE category_id = ' . $v['category_id'];
                        $select = $connection->select()->from(['ves_blog_category' => $this->getTable('ves_blog_category')])
                        ->where('ves_blog_category.category_id = ' . $v['category_id'])
                        ->order('ves_blog_category.cat_position DESC');
                        $category = $connection->fetchRow($select);

                        $tmp[] = $category;
                        unset($categories[$k]);
                    }
                }
                $item->setData('categories', $tmp);
            }
        }
    }

    protected function getCommentsAfterLoad()
    {
        $connection = $this->getConnection();
        foreach ($this as $item) {
            $select = 'SELECT * FROM ' . $this->getTable('ves_blog_comment') . ' WHERE post_id = ' . $item->getData("post_id");
            $count = count($connection->fetchAll($select));
            $item->setData('comment_count', $count);
        }
    }

    protected function getTagsAfterLoad()
    {
        $connection = $this->getConnection();
        foreach ($this as $item) {
            $select = 'SELECT * FROM ' . $this->getTable('ves_blog_post_tag') . ' WHERE post_id = ' . $item->getData("post_id");
            $tags = $connection->fetchAll($select);
            $item->setData('tag', $tags);
        }
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ves\Blog\Model\Post', 'Ves\Blog\Model\ResourceModel\Post');
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * Returns pairs post_id - title
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('post_id', 'title');
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('ves_blog_post_store', 'post_id');
    }

    /**
     * Add link attribute to filter.
     *
     * @param string $code
     * @param array $condition
     * @return $this
     */
    public function addLinkAttributeToFilter($code, $condition)
    {
        foreach ($this->getLinkAttributes() as $attribute) {
            if ($attribute['code'] == $code) {
                $alias = sprintf('link_attribute_%s_%s', $code, $attribute['type']);
                $whereCondition = $this->_getConditionSql($alias . '.`value`', $condition);
                $this->getSelect()->where($whereCondition);
            }
        }
        return $this;
    }
}
