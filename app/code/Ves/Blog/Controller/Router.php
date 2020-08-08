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
 * @copyright  Copyright (c) 2016 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Blog\Controller;

use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Url;

class Router implements RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Event manager
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * Response
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var bool
     */
    protected $dispatched;

    protected $_brandCollection;

    protected $_groupCollection;

    /**
     * Store manager
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Ves\Blog\Model\Category
     */
    protected $_category;

    /**
     * @var \Ves\Blog\Model\Tag
     */
    protected $_tag;
    protected $_post;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    protected $_user;

    /**
     * @param ActionFactory
     * @param ResponseInterface
     * @param ManagerInterface
     * @param StoreManagerInterface
     * @param \Ves\Blog\Helper\Data
     * @param \Ves\Blog\Model\Category
     * @param \Ves\Blog\Model\Post
     * @param \Ves\Blog\Model\Tag
     * @param \Magento\User\Model\UserFactory
     * @param \Magento\Framework\Registry
     */
    public function __construct(
        ActionFactory $actionFactory,
        ResponseInterface $response,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        \Ves\Blog\Helper\Data $blogHelper,
        \Ves\Blog\Model\Category $category,
        \Ves\Blog\Model\Post $post,
        \Ves\Blog\Model\Tag $tag,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Framework\Registry $registry
        )
    {
        $this->actionFactory = $actionFactory;
        $this->eventManager  = $eventManager;
        $this->response      = $response;
        $this->storeManager  = $storeManager;
        $this->_blogHelper   = $blogHelper;
        $this->_category     = $category;
        $this->_post         = $post;
        $this->_tag          = $tag;
        $this->_user         = $userFactory;
        $this->_coreRegistry = $registry;

    }

    function startsWith($haystack, $needle) {
        if($haystack && $needle){
            return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
        }
        return true;
    }

    function endsWith($haystack, $needle) {
        if($haystack && $needle){
            return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
        }
        return true;
    }

    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface
     */
    public function match(RequestInterface $request)
    {
        $_blogHelper = $this->_blogHelper;
        $store = $this->storeManager->getStore();
        if (!$this->dispatched) {
            $urlKey = trim($request->getPathInfo(), '/');
           
            $origUrlKey = $urlKey;
            /** @var Object $condition */
            $condition = new DataObject(['url_key' => $urlKey, 'continue' => true]);
            $this->eventManager->dispatch(
                'ves_blog_controller_router_match_before',
                [
                'router' => $this,
                'condition' => $condition
                ]
                );
            $urlKey = $condition->getUrlKey();
           
            if ($condition->getRedirectUrl()) {
                $this->response->setRedirect($condition->getRedirectUrl());
                $request->setDispatched(true);
                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Redirect',
                    ['request' => $request]
                    );
            }
            if (!$condition->getContinue()) {
                return null;
            }

            $urlPrefix = $_blogHelper->getConfig('general_settings/url_prefix');
            $urlSuffix = $_blogHelper->getConfig('general_settings/url_suffix');
            $categoriesUrls = $_blogHelper->getConfig('general_settings/categories_urls');
            $urlKeys = explode("/", $urlKey);
            $latestPageRoute = $_blogHelper->getConfig('blog_latest_page/route');
            $urlKeysOrgin = $urlKeys;

            if($urlPrefix == '') {
                $urlKeys[1] = $urlKeysOrgin[0];
                $urlKeys[0] = '';
            }

            // LATEST PAGE
            if(count($urlKeys) == 1 && $urlPrefix != '' && (str_replace($urlSuffix, "", $urlKeys[0]) == $urlPrefix)){
                $request->setModuleName('vesblog')
                    ->setControllerName('latest')
                    ->setActionName('view');
                    $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                    $request->setDispatched(true);
                    $this->dispatched = true;
                    return $this->actionFactory->create(
                        'Magento\Framework\App\Action\Forward',
                        ['request' => $request]
                        );
            }

            // AUTHOR PAGE
            if(count($urlKeys)==3 && $urlPrefix == $urlKeys[0] && $urlKeys[1]=='author'){
                $alias = str_replace($urlSuffix, "", $urlKeys[2]);

                $author = $this->_user->create()->loadByUsername($alias);
                $this->_coreRegistry->register("author", $author);
                if($author){
                    $request->setModuleName('vesblog')
                    ->setControllerName('author')
                    ->setActionName('view');
                    $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                    $request->setDispatched(true);
                    $this->dispatched = true;
                    return $this->actionFactory->create(
                        'Magento\Framework\App\Action\Forward',
                        ['request' => $request]
                        );
                }
            }
            

            // CATEGORY PAGE
            if(count($urlKeys)==2 && $urlPrefix == $urlKeys[0] && $urlKeys[1]!='' && $this->endsWith($urlKeys[1], $urlSuffix)){
                $alias = str_replace($urlSuffix, "", $urlKeys[1]);              
                $category = $this->_category->getCollection()
                ->addFieldToFilter('identifier', $alias)
                ->addFieldToFilter('is_active', 1)
                ->addStoreFilter($store)
                ->getFirstItem();
                if(isset($urlKeys[1]) && $category->getData()){
                    $this->_coreRegistry->register("current_post_category", $category);
                    $request->setModuleName('vesblog')
                    ->setControllerName('category')
                    ->setActionName('view');
                    $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                    $request->setDispatched(true);
                    $this->dispatched = true;
                    return $this->actionFactory->create(
                        'Magento\Framework\App\Action\Forward',
                        ['request' => $request]
                        );
                }
            }

            // ARCHIVE PAGE
            if($urlPrefix == '' && count($urlKeysOrgin)==2) {
                $urlKeys[2] = $urlKeysOrgin[1];
                $urlKeys[1] = $urlKeysOrgin[0];
                $urlKeys[0] = '';
            }
            if(count($urlKeys)==3 && $urlPrefix == $urlKeys[0] && $urlKeys[1]=='archive'){
                $alias = str_replace($urlSuffix, "", $urlKeys[2]);
                $date = explode('-', $alias);
                $date[2] = '01';
                $time = strtotime(implode('-', $date));
                if (!$time || count($date) != 3) {
                    return;
                }
                if($this->_coreRegistry->registry('current_archive')){
                    $this->_coreRegistry->register("current_archive", $alias);
                }
                $request->setModuleName('vesblog')
                    ->setControllerName('archive')
                    ->setActionName('view')
                    ->setParam('date', $alias);
                $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                $request->setDispatched(true);
                $this->dispatched = true;
                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Forward',
                    ['request' => $request]
                );
            }

            // TAG PAGE
            if($urlPrefix == '' && count($urlKeysOrgin)==2) {
                $urlKeys[2] = $urlKeysOrgin[1];
                $urlKeys[1] = $urlKeysOrgin[0];
                $urlKeys[0] = '';
            }
            if(count($urlKeys)==3 && $urlPrefix == $urlKeys[0] && $urlKeys[1]=='tag'){
                $alias = str_replace($urlSuffix, "", $urlKeys[2]);
                $tagCollection = $this->_tag->getCollection()
                ->addFieldToFilter("alias", $alias);
                if(!empty($tagCollection)){

                    $tag = $tagCollection->getFirstItem();

                    $postIds = [];
                    foreach ($tagCollection as $k => $v) {
                        $postIds[] = $v['post_id'];
                    }
                    $this->_coreRegistry->register("postIds", $postIds);
                    $this->_coreRegistry->register("tag_key", $alias);
                    $this->_coreRegistry->register("tag_name", $tag->getName());
                    $request->setModuleName('vesblog')
                    ->setControllerName('tag')
                    ->setActionName('view')
                    ->setParam('tag_key', $alias)
                    ->setParam('tag_name', $tag->getName())
                    ->setParam('is_tag', (!empty($postIds)?true:false) );
                    $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                    $request->setDispatched(true);
                    $this->dispatched = true;
                    return $this->actionFactory->create(
                        'Magento\Framework\App\Action\Forward',
                        ['request' => $request]
                        );
                }
            }

            // SEARCH PAGE
            if($urlPrefix == '') {
                $urlKeys[1] = $urlKeysOrgin[0];
                $urlKeys[0] = '';
            }
            if(count($urlKeys)==2 && $urlPrefix == $urlKeys[0] && $urlKeys[1]=='search'){
                $request->setModuleName('vesblog')
                ->setControllerName('search')
                ->setActionName('result');
                $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                $request->setDispatched(true);
                $this->dispatched = true;
                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Forward',
                    ['request' => $request]
                    );
            }
            if($urlPrefix == '') $urlKeys = [];
            if(count($urlKeysOrgin) == 3 && $urlPrefix == '' && $categoriesUrls) {
                $urlKeys[0] = '';
                $urlKeys[1] = $urlKeysOrgin[0];
                $urlKeys[2] = $urlKeysOrgin[1];
            }

            if(count($urlKeysOrgin) == 2 && $urlPrefix == '' && $categoriesUrls) {
                $urlKeys[0] = '';
                $urlKeys[1] = '';
                $urlKeys[2] = $urlKeysOrgin[1];
            }

            if(count($urlKeysOrgin) == 2 && $urlPrefix != '' && !$categoriesUrls) {
                $urlKeys[0] = $urlKeysOrgin[0];
                $urlKeys[1] = '';
                $urlKeys[2] = $urlKeysOrgin[1];
            }
            // POST PAGE
            if(count($urlKeys)==3 && $urlPrefix == $urlKeys[0] && $urlKeys[2]!='' && $this->endsWith($urlKeys[2], $urlSuffix)){
                $alias = str_replace($urlSuffix, "", $urlKeys[2]);
                $post = $this->_post->getCollection()
                ->addFieldToFilter("is_active", 1)
                ->addFieldToFilter("identifier", $alias)
                ->addStoreFilter($store)
                ->getFirstItem();
                if(!empty($post->getData())){
                    $this->_coreRegistry->register("current_post", $post);
                    $request->setModuleName('vesblog')
                    ->setControllerName('post')
                    ->setActionName('view');
                    $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                    $request->setDispatched(true);
                    $this->dispatched = true;
                    return $this->actionFactory->create(
                        'Magento\Framework\App\Action\Forward',
                        ['request' => $request]
                        );
                }
            }
        }
    }
}