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
namespace Ves\Blog\Block\Rss;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Comment extends \Magento\Framework\View\Element\AbstractBlock implements DataProviderInterface
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Catalog\Model\Rss\Category
     */
    protected $rssModel;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface
     */
    protected $rssUrlBuilder;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Ves\Blog\Model\Post
     */
    protected $_post;

    /**
     * @var \Ves\Blog\Model\Tag
     */
    protected $_tag;

    /**
     * @var \Ves\Blog\Model\Comment
     */
    protected $_comment;

    protected $_blogHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\Rss\Category $rssModel
     * @param \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CategoryRepositoryInterface $categoryRepository
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\Rss\Category $rssModel,
        \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder,
        \Ves\Blog\Helper\Image $imageHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Ves\Blog\Model\Category $categoryRepository,
        \Ves\Blog\Model\Post $post,
        \Ves\Blog\Model\Tag $tag,
        \Ves\Blog\Model\Comment $comment,
        \Ves\Blog\Helper\Data $blogHelper,
        array $data = []
    ) {
        $this->imageHelper        = $imageHelper;
        $this->categoryFactory    = $categoryFactory;
        $this->customerSession    = $customerSession;
        $this->rssModel           = $rssModel;
        $this->rssUrlBuilder      = $rssUrlBuilder;
        $this->storeManager       = $context->getStoreManager();
        $this->categoryRepository = $categoryRepository;
        $this->_post              = $post;
        $this->_tag               = $tag;
        $this->_comment           = $comment;
        $this->_blogHelper        = $blogHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->setCacheKey(
            'rss_blog_latest_'
            . $this->getRequest()->getParam('key') . '_'
            . $this->getStoreId() . '_'
            . $this->customerSession->getId()
        );
        parent::_construct();
    }

    /**
     * {@inheritdoc}
     */
    public function getRssData()
    {
        $postIds = [];
        $storeId = $this->getStoreId();
        $store = $this->storeManager->getStore($storeId);

        $postId = (int)$this->getRequest()->getParam('key');
        $post = $this->_post->getCollection()
        ->addFieldToFilter("is_active", 1)
        ->addFieldToFilter("main_table.post_id", $postId)
        ->getFirstItem();

        $commentrss = $this->_blogHelper->getConfig("general_settings/commentrss");
        if(!$post || !$commentrss){
            return [
                'title' => 'Comments Not Found',
                'description' => 'Comments Not Found',
                'link' => $this->getUrl(''),
                'charset' => 'UTF-8'
            ];
        }

        $newUrl = $post->getUrl();
        $title = $post->getTitle();
        $data = ['title' => $title, 'description' => $title, 'link' => $newUrl, 'charset' => 'UTF-8'];

        $itemsperpage = $this->_blogHelper->getConfig("post_page/rssposts_show");
        $collection = $this->_comment->getCollection();
        $collection->addFieldToFilter("is_active", 1)
        ->addFieldToFilter("post_id", $post->getPostId())
        ->setOrder("comment_id", "DESC");

        foreach ($collection as $_comment) {
            $description = '
                    <table><tr>
                        <td>'  . $_comment->getContent() . '</td>
                    </tr></table>
                ';
            $data['entries'][] = [
                'title' => $this->_blogHelper->subString($_comment->getContent(), 130),
                'link' => $post->getUrl() . '#comment' . $_comment->getCommentId(),
                'description' => $description,
            ];
        }
        return $data;
    }

    /**
     * Get rendered price html
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function renderPriceHtml(\Magento\Catalog\Model\Product $product)
    {
        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->getLayout()->createBlock(
                'Magento\Framework\Pricing\Render',
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                [
                    'display_minimal_price'  => true,
                    'use_link_for_as_low_as' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
                ]
            );
        }

        return $price;
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        $storeId = (int)$this->getRequest()->getParam('store_id');
        if ($storeId == null) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $storeId;
    }

    /**
     * @return int
     */
    public function getCacheLifetime()
    {
        return 600;
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowed()
    {
        return $this->_scopeConfig->isSetFlag(
            'rss/catalog/category',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array
     */
    public function getFeeds()
    {
        $result = [];
        if ($this->isAllowed()) {
            /** @var $category \Magento\Catalog\Model\Category */
            $category = $this->categoryFactory->create();
            $treeModel = $category->getTreeModel()->loadNode($this->storeManager->getStore()->getRootCategoryId());
            $nodes = $treeModel->loadChildren()->getChildren();

            $nodeIds = [];
            foreach ($nodes as $node) {
                $nodeIds[] = $node->getId();
            }

            /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
            $collection = $category->getResourceCollection();
            $collection->addIdFilter($nodeIds)
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('is_anchor')
                ->addAttributeToFilter('is_active', 1)
                ->addAttributeToSort('name')
                ->load();

            $feeds = [];
            foreach ($collection as $category) {
                $feeds[] = [
                    'label' => $category->getName(),
                    'link' => $this->rssUrlBuilder->getUrl(['type' => 'category', 'cid' => $category->getId()]),
                ];
            }
            $result = ['group' => 'Categories', 'feeds' => $feeds];
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthRequired()
    {
        return false;
    }
}
