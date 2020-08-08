<?php
namespace Potato\Crawler\Model\Command;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use Potato\Crawler\Model\Manager\QueueManager;
use Potato\Crawler\Model\Cron\Queue as CronQueue;
use Magento\Store\Model\StoreManagerInterface;
use Potato\Crawler\Logger\Logger;
use Magento\Framework\App\CacheInterface;
use Potato\Crawler\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedType;

class Queue extends Command
{
    /** @var Logger  */
    protected $logger;

    /** @var CacheInterface  */
    protected $cache;

    /** @var StoreManagerInterface  */
    protected $storeManager;
    
    /** @var CronQueue */
    protected $cronQueue;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /** @var ConfigurableType  */
    protected $configurableType;

    /** @var GroupedType  */
    protected $groupedType;

    protected $queueCollectionFactory;

    private $state;

    /**
     * Queue constructor.
     * @param StoreManagerInterface $storeManager
     * @param CronQueue $cronQueue
     * @param CacheInterface $cache
     * @param Logger $logger
     * @param null $name
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        QueueCollectionFactory $queueCollectionFactory,
        CronQueue $cronQueue,
        CacheInterface $cache,
        CategoryRepositoryInterface $categoryRepository,
        ProductRepositoryInterface $productRepository,
        ConfigurableType $configurableType,
        GroupedType $groupedType,
        Logger $logger,
        \Magento\Framework\App\State $state,
        $name = null
    ) {
        parent::__construct($name);
        $this->storeManager = $storeManager;
        $this->cronQueue = $cronQueue;
        $this->logger = $logger;
        $this->cache = $cache;
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->configurableType = $configurableType;
        $this->groupedType = $groupedType;
        $this->state = $state;
    }

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('po_crawler:queue')
            ->setDescription('Potato Crawler: add website links to crawler queue')
        ;
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //process should work without time limit
        ini_set('max_execution_time', -1);
        //Area code not set: Area code must be set before starting a session.
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        $this
            ->addAll()
            ->addSpecified()
        ;
        return true;
    }

    /**
     * @return $this
     */
    protected function addSpecified()
    {
        if ($cmsIds = $this->cache->load(QueueManager::CACHE_QUEUE_CMS_FLAG)) {
            $this->removeCache(QueueManager::CACHE_QUEUE_CMS_FLAG);
            $this->addCmsToQueue($cmsIds);
        }
        if ($productIds = $this->cache->load(QueueManager::CACHE_QUEUE_PRODUCT_FLAG)) {
            $this->removeCache(QueueManager::CACHE_QUEUE_PRODUCT_FLAG);
            $this->addProductToQueue($productIds);
        }
        if ($categoryIds = $this->cache->load(QueueManager::CACHE_QUEUE_CATEGORY_FLAG)) {
            $this->removeCache(QueueManager::CACHE_QUEUE_CATEGORY_FLAG);
            $this->addCategoryToQueue($categoryIds);
        }

        if ($storeIds = $this->cache->load(QueueManager::CACHE_QUEUE_STORES_FLAG)) {
            $this->removeCache(QueueManager::CACHE_QUEUE_STORES_FLAG);
            $storeIds = array_unique(unserialize($storeIds));
            foreach ($storeIds as $storeId) {
                try {
                    $store = $this->storeManager->getStore($storeId);
                    $this->cronQueue->addStoreUrls($store);
                } catch (\Exception $e) {
                    $this->logger->customError($e);
                }
            }
        }
        return $this;
    }

    protected function addProductToQueue($ids)
    {
        $ids = array_unique(unserialize($ids));
        $result = [
            'category' => [],
            'product' => [],
            'cms' => [-1]
        ];
        foreach ($ids as $id) {
            try {
                $product = $this->productRepository->getById($id);
            } catch (\Exception $e) {
                continue;
            }
            if (is_array($product->getCategoryIds())) {
                $result['category'] = array_merge($result['category'], $product->getCategoryIds());
            }
            $this->getParentProductIds($product, $result);
        }
        try {
            $this->cronQueue->addSpecified($result);
        } catch (\Exception $e) {
            $this->logger->customError($e);
        }
        return $this;
    }

    public function getParentProductIds($product, &$result)
    {
        if ($product->getTypeId() == ProductType::TYPE_SIMPLE) {
            $configurableIds = $this->configurableType->getParentIdsByChild($product->getId());
            $groupedIds = $this->groupedType->getParentIdsByChild($product->getId());
            $productIds = array_merge($configurableIds, $groupedIds);
        } else {
            $productIds = $product->getTypeInstance()->getParentIdsByChild($product->getId());
        }
        array_merge($result['product'], $productIds);
        $result['product'][] = $product->getId();
        foreach ($productIds as $productId) {
            try {
                $product = $this->productRepository->getById($productId);
            } catch (\Exception $e) {
                continue;
            }
            if (is_array($product->getCategoryIds())) {
                $result['category'] = array_merge($result['category'], $product->getCategoryIds());
            }
        }
        return $this;
    }

    protected function addCmsToQueue($ids)
    {
        $ids = array_unique(unserialize($ids));
        try {
            $this->cronQueue->addSpecified(['cms' => $ids, 'category' => -1, 'product' => -1]);
        } catch (\Exception $e) {
            $this->logger->customError($e);
        }
        return $this;
    }

    protected function addCategoryToQueue($categoryIds)
    {
        $categoryIds = array_unique(unserialize($categoryIds));
        foreach ($categoryIds as $categoryId) {
            try {
                $ids = $this->getCategoryChild($categoryId);
                $ids['category'][] = $categoryId;
                $ids['cms'][] = -1;
                $this->cronQueue->addSpecified($ids);
            } catch (\Exception $e) {
                $this->logger->customError($e);
            }
        }
        return $this;
    }

    protected function getCategoryChild($categoryId)
    {
        /**
         * add category products
         */
        try {
            $category = $this->categoryRepository->get($categoryId);
            $categoryIds = $category->getAllChildren(true);
            $ids = $this->getCategoryProducts($categoryIds);
        } catch (\Exception $e) {
            return [
                'category' => [$categoryId],
                'product'  => [-1]
            ];
        }
        return $ids;
    }

    protected function getCategoryProducts($categoryIds)
    {
        $result = [
            'category' => [],
            'product' => [],
        ];
        foreach ($categoryIds as $categoryId) {
            try {
                $result['category'][] = $categoryId;
                $category = $this->categoryRepository->get($categoryId);
                $productIds = $category->getProductCollection()->getAllIds();
                foreach ($productIds as $productId) {
                    $result['product'][] = $productId;
                }
            } catch (\Exception $e) {

            }
        }
        return $result;
    }

    /**
     * @return $this
     */
    protected function addAll()
    {
        if (!$this->cache->load(QueueManager::CACHE_QUEUE_ALL_FLAG)) {
            return $this;
        }
        try {
            $this->removeCache(QueueManager::CACHE_QUEUE_ALL_FLAG);
            $this->cronQueue->process();
        } catch (\Exception $e) {
            $this->logger->customError($e);
        }
        return $this;
    }

    /**
     * @param $index
     * @return $this
     */
    protected function removeCache($index)
    {
        $this->cache->remove($index);
        return $this;
    }
}