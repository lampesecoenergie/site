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
namespace Ves\Blog\Block\Adminhtml\Post\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('post_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Post Information'));

        $this->addTab(
                'main_section',
                [
                    'label' => __('General'),
                    'content' => $this->getLayout()->createBlock('Ves\Blog\Block\Adminhtml\Post\Edit\Tab\Main')->toHtml()
                ]
            );

        $this->addTab(
                'content_section',
                [
                    'label' => __('Content'),
                    'content' => $this->getLayout()->createBlock('Ves\Blog\Block\Adminhtml\Post\Edit\Tab\Content')->toHtml()
                       
                ]
            );

        $this->addTab(
                'design_section',
                [
                    'label' => __('Design'),
                    'content' => $this->getLayout()->createBlock('Ves\Blog\Block\Adminhtml\Post\Edit\Tab\Design')->toHtml()
                ]
            );

        $this->addTab(
                'meta_section',
                [
                    'label' => __('SEO'),
                    'content' => $this->getLayout()->createBlock('Ves\Blog\Block\Adminhtml\Post\Edit\Tab\Meta')->toHtml()
                ]
            );

        $this->addTab(
                'comments',
                [
                    'label' => __('Comments'),
                    'url' => $this->getUrl('vesblog/*/comments', ['_current' => true]),
                    'class' => 'ajax'
                ]
            );

        $this->addTab(
                'related',
                [
                    'label' => __('Posts Related'),
                    'url' => $this->getUrl('vesblog/*/related', ['_current' => true]),
                    'class' => 'ajax'
                ]
            );

        $this->setActiveTab("post_tabs_meta_section");
    }
}
