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
namespace Ves\Blog\Block\Adminhtml\Category\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('category_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Category Information'));

        $this->addTab(
                'main_section',
                [
                    'label' => __('Category Information'),
                    'content' => $this->getLayout()->createBlock('Ves\Blog\Block\Adminhtml\Category\Edit\Tab\Main')->toHtml()
                ]
            );

        $this->addTab(
                'design_section',
                [
                    'label' => __('Design'),
                    'content' => $this->getLayout()->createBlock('Ves\Blog\Block\Adminhtml\Category\Edit\Tab\Design')->toHtml()
                ]
            );

        $this->addTab(
                'meta_section',
                [
                    'label' => __('SEO'),
                    'content' => $this->getLayout()->createBlock('Ves\Blog\Block\Adminhtml\Category\Edit\Tab\Meta')->toHtml()
                ]
            );

        $this->addTab(
                'posts',
                [
                    'label' => __('Posts'),
                    'url' => $this->getUrl('vesblog/category/posts', ['_current' => true]),
                    'class' => 'ajax'
                ]
            );
    }
}
