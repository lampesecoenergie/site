<?php
namespace Potato\Crawler\Model\ResourceModel\Sitemap\Cms;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Cms\Model\Page as CmsModelPage;
use Magento\Store\Model\StoreManagerInterface;
use Potato\Crawler\Api as Api;

class Page extends \Magento\Sitemap\Model\ResourceModel\Cms\Page
    implements Api\CmsPageSitemapManagerInterface
{
    protected $ids = null;

    /** @var StoreManagerInterface  */
    protected $storeManager;
    
    /**
     * Page constructor.
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param EntityManager $entityManager
     * @param StoreManagerInterface $storeManager
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        EntityManager $entityManager,
        StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        parent::__construct($context, $metadataPool, $entityManager, $connectionName);
        $this->storeManager = $storeManager;
    }

    /**
     * @param int $store
     * @return array
     */
    public function getCollection($store)
    {
        $entityMetadata = $this->metadataPool->getMetadata(PageInterface::class);
        $linkField = $entityMetadata->getLinkField();
        
        $store = $this->storeManager->getStore($store);
        $storeId = $store->getId() ?: 0;
        
        $select = $this->getConnection()->select()->from(
            ['main_table' => $this->getMainTable()],
            [$this->getIdFieldName(), 'url' => 'identifier', 'updated_at' => 'update_time']
        )->join(
            ['store_table' => $this->getTable('cms_page_store')],
            "main_table.{$linkField} = store_table.$linkField",
            []
        )->where(
            'main_table.is_active = 1'
        )->where(
            'main_table.identifier != ?',
            CmsModelPage::NOROUTE_PAGE_ID
        )->where(
            'store_table.store_id IN(?)',
            [0, $storeId]
        );

        if (!empty($this->ids)) {
            $select->where('main_table.page_id IN(?)', $this->ids);
        }

        $pages = [];
        $query = $this->getConnection()->query($select);
        while ($row = $query->fetch()) {
            if ($row['url'] == "home") {
                $row['url'] = "";
            }
            $page = $this->_prepareObject($row);
            $pages[$page->getId()] = $page;
        }
        return $pages;
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