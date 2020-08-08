<?php
namespace Potato\Compressor\Block;

use Magento\Framework\View\Element\Template;
use Potato\Compressor\Model\Config;
use Potato\Compressor\Helper\Data as DataHelper;

class FastInit extends Template
{
    /** @var Config  */
    protected $config;

    /**
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
     * @return string
     */
    public function getDataAttribute()
    {
        return DataHelper::DATA_ATTRIBUTE_FOR_FAST_INIT;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->config->isImageProductGallerySpeedUp()) {
            return '';
        }
        if (!$this->config->isRequestAvailableForModule($this->getRequest())) {
            return '';
        }
        return parent::_toHtml();
    }
}