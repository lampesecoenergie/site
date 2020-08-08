<?php
namespace Ves\Blog\Block\Tag\Rss;

class Link extends \Magento\Framework\View\Element\Template
{
	/**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry = null;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface
     */
    protected $rssUrlBuilder;

    /**
     * @var \Ves\Blog\Helper\Data
     */
    protected $_bloghelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder,
        \Magento\Framework\Registry $registry,
        \Ves\Blog\Helper\Data $blogHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->rssUrlBuilder = $rssUrlBuilder;
        $this->_bloghelper = $blogHelper;
        parent::__construct($context, $data);
    }

    public function _toHtml(){
        if(!$this->_bloghelper->getConfig("general_settings/tagrss")) return;
        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function isRssAllowed()
    {
        return true;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Subscribe to RSS Feed');
    }

    /**
     * @return string
     */
    protected function getLinkParams()
    {
        return [
            'type' => 'post_tag',
            'key' => $this->registry->registry('tag_key'),
        ];
    }  

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->getUrl("vesblog/feed/index",$this->getLinkParams());
    }
}