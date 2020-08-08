<?php
namespace Potato\Crawler\Model\ResourceModel\Sitemap\Catalog;

use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\EntityManager\MetadataPool;
use Potato\Crawler\Api as Api;

class Category extends \Magento\Sitemap\Model\ResourceModel\Catalog\Category
    implements Api\CategorySitemapManagerInterface
{
    protected $ids = null;

    /**
     * Category constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param CategoryResource $categoryResource
     * @param MetadataPool $metadataPool
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        CategoryResource $categoryResource,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        parent::__construct($context, $storeManager, $categoryResource, $metadataPool, $connectionName);
    }

    /**
     * @param int $store
     * @return array
     */
    public function getCollection($store)
    {
        $categories = [];

        /* @var $store \Magento\Store\Model\Store */
        $store = $this->_storeManager->getStore($store);

        if (!$store) {
            return false;
        }

        $connection = $this->getConnection();

        $this->_select = $connection->select()->from(
            $this->getMainTable()
        )->where(
            $this->getIdFieldName() . '=?',
            $store->getRootCategoryId()
        );
        $categoryRow = $connection->fetchRow($this->_select);

        if (!$categoryRow) {
            return false;
        }

        $this->_select = $connection->select()->from(
            ['e' => $this->getMainTable()],
            [$this->getIdFieldName(), 'updated_at']
        )->joinLeft(
            ['url_rewrite' => $this->getTable('url_rewrite')],
            'e.entity_id = url_rewrite.entity_id AND url_rewrite.is_autogenerated = 1'
            . $connection->quoteInto(' AND url_rewrite.store_id = ?', $store->getId())
            . $connection->quoteInto(' AND url_rewrite.entity_type = ?', CategoryUrlRewriteGenerator::ENTITY_TYPE),
            ['url' => 'request_path']
        )->where(
            'e.path LIKE ?',
            $categoryRow['path'] . '/%'
        );

        if (!empty($this->ids)) {
            $this->_select->where('e.entity_id IN(?)', $this->ids);
        }

        $this->_addFilter($store->getId(), 'is_active', 1);

        $query = $connection->query($this->_select);
        while ($row = $query->fetch()) {
            $category = $this->_prepareCategory($row);
            $categories[$category->getId()] = $category;
        }
        return $categories;
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function addFilterByIds($ids)
    {
        $this->ids = $ids;
        return $this;
    }
}