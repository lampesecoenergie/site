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
 * @package    Ves_Themesettings
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Themesettings\Block\Html;

class Footer extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Block factory
     *
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $_blockFactory;

    /**
     *
     * @var \Ves\Themesettings\Helper\Theme
     */
    protected $_ves;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Ves\Themesettings\Helper\Theme $ves,
        array $data = []
    ) {
        $this->_filterProvider = $filterProvider;
        $this->_blockFactory = $blockFactory;
        $this->_ves = $ves;
        parent::__construct($context, $data);
    }
    public function getRequestParam( $param, $alias_param ="", $default = null) {
        return $this->getRequest()->getParam($param, $this->getRequest()->getParam($alias_param, $default));
    }
    public function getStaticBlock($blockId){
        $html = '';
        if($this->_ves->getGeneralCfg("general_settings/paneltool")){
            $blockId_tmp = $this->getRequest()->getParam('footer_layout', $this->getRequest()->getParam('footer', false));
            if($blockId_tmp) {
                $blockId = trim($blockId_tmp);
            }
        }

        if ($blockId) {
            $storeId = $this->_storeManager->getStore()->getId();
            /** @var \Magento\Cms\Model\Block $block */
            $block = $this->_blockFactory->create();
            if(is_numeric($blockId)) {
                $block->setStoreId($storeId)->load((int)$blockId);
            } else {
                $block->setStoreId($storeId)->getBlockByAlias($blockId);
            }
            
            if ($block->isActive()) {
                $html = $this->_filterProvider->getBlockFilter()->setStoreId($storeId)->filter($block->getContent());
            }
        }
        return $html;
    }
}