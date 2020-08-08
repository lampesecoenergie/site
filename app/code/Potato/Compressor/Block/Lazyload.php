<?php
namespace Potato\Compressor\Block;

use Magento\Framework\View\Element\Template;
use Potato\Compressor\Model\Config;

class Lazyload extends Template
{
    /** @var Config  */
    protected $config;

    /**
     * Lazyload constructor.
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        array $data = []
    ) {
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isOnScrollLoad()
    {
        return $this->config->isImageLazyLoadOnVisibleMode();
    }

    /**
     * @retrun string[]
     */
    public function getExcludeImagesByCSSSelector()
    {
        return $this->config->getExcludeImagesFromLazyLoad();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->config->isImageLazyLoadEnabled()) {
            return '';
        }
        if (!$this->config->isRequestAvailableForModule($this->getRequest())) {
            return '';
        }
        return parent::_toHtml();
    }
}