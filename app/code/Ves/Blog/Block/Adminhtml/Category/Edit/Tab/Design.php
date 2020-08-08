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
namespace Ves\Blog\Block\Adminhtml\Category\Edit\Tab;

class Design extends \Magento\Backend\Block\Widget\Form\Generic implements
\Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Framework\View\Design\Theme\LabelFactory
     */
    protected $_labelFactory;

    /**
     * @var \Magento\Theme\Model\Layout\Source\Layout
     */
    protected $_pageLayout;

    protected $_templateLayout;

    /**
     * @var \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
     */
    protected $pageLayoutBuilder;
    protected $_orderby;
    protected $_gridColumns;
    protected $_vlayout;

    /**
     * @param \Magento\Backend\Block\Template\Context
     * @param \Magento\Framework\Registry
     * @param \Magento\Framework\Data\FormFactory
     * @param \Magento\Theme\Model\Layout\Source\Layout
     * @param \Magento\Framework\View\Design\Theme\LabelFactory
     * @param \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
     * @param \Ves\Blog\Model\Config\Source\CatOrderby
     * @param \Ves\Blog\Model\Config\Source\Gridcolumns
     * @param \Ves\Blog\Model\Config\Source\Layout
     * @param array
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Theme\Model\Layout\Source\Layout $pageLayout,
        \Magento\Framework\View\Design\Theme\LabelFactory $labelFactory,
        \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder,
        \Ves\Blog\Model\Config\Source\CatOrderby $orderby,
        \Ves\Blog\Model\Config\Source\Gridcolumns $gridColumns,
        \Ves\Blog\Model\Config\Source\Layout $layout,
        array $data = []
        ) {
        $this->pageLayoutBuilder = $pageLayoutBuilder;
        $this->_labelFactory = $labelFactory;
        $this->_pageLayout = $pageLayout;
        $this->_orderby = $orderby;
        $this->_gridColumns = $gridColumns;
        $this->_vlayout = $layout;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form tab configuration
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setShowGlobalIcon(true);
    }

    /**
     * Initialise form fields
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /*
         * Checking if user have permissions to save information
         */
        $isElementDisabled = !$this->_isAllowedAction('Ves_Blog::category_save');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(['data' => ['html_id_prefix' => 'category_']]);

        $model = $this->_coreRegistry->registry('blog_category');

        $templateFieldset = $form->addFieldset(
            'template_fieldset',
            [
                'legend'   => __('Page Template'),
                'class'    => 'fieldset-wide',
                'disabled' => $isElementDisabled
            ]
            );

        $templateFieldset->addField(
            'layout_type',
            'select',
            [
                'name'     => 'layout_type',
                'label'    => __('Type'),
                'values'   => $this->_vlayout->toOptionArray(),
                'disabled' => $isElementDisabled
            ]
            );

        $templateFieldset->addField(
            'orderby',
            'select',
            [
                'name'     => 'orderby',
                'label'    => __('Order By'),
                'values'   => $this->_orderby->toOptionArray(),
                'disabled' => $isElementDisabled
            ]
            );

        $templateFieldset->addField(
            'item_per_page',
            'text',
            [
                'name'     => 'item_per_page',
                'label'    => __('Number Post Per Page'),
                'disabled' => $isElementDisabled
            ]
            );

        $templateFieldset->addField(
            'lg_column_item',
            'select',
            [
                'name'     => 'lg_column_item',
                'label'    => __('Number Column on Large Desktop'),
                'values'   => $this->_gridColumns->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note'     => __('Large devices Desktops (≥1200px). Use in grid layout and masonry layout.')
            ]
            );

        $templateFieldset->addField(
            'md_column_item',
            'select',
            [
                'name'     => 'md_column_item',
                'label'    => __('Number Column on Large Desktop'),
                'values'   => $this->_gridColumns->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note'     => __('Medium devices Desktops (≥992px)')
            ]
            );

        $templateFieldset->addField(
            'sm_column_item',
            'select',
            [
                'name'     => 'sm_column_item',
                'label'    => __('Number Column on Tablets'),
                'values'   => $this->_gridColumns->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note'     => __('Small devices Tablets (≥768px)')
            ]
            );

        $templateFieldset->addField(
            'xs_column_item',
            'select',
            [
                'name'     => 'xs_column_item',
                'label'    => __('Number Column on Large Desktop'),
                'values'   => $this->_gridColumns->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note'     => __('Extra small devices Phones (<768px)')
            ]
            );


        $layoutFieldset = $form->addFieldset(
            'layout_fieldset',
            [
                'legend'   => __('Page Layout'),
                'class'    => 'fieldset-wide',
                'disabled' => $isElementDisabled
            ]
            );

        $layoutFieldset->addField(
            'page_layout',
            'select',
            [
                'name'     => 'page_layout',
                'label'    => __('Page Layout'),
                'values'   => $this->pageLayoutBuilder->getPageLayoutsConfig()->toOptionArray(),
                'disabled' => $isElementDisabled
            ]
            );
        if (!$model->getId()) {
            $model->setData('page_layout' ,'2columns-left');
            $model->setData('item_per_page' ,20);
            $model->setData('lg_column_item' ,4);
            $model->setData('md_column_item' ,4);
            $model->setData('sm_column_item' ,2);
            $model->setData('xs_column_item' ,1);
        }

        $layoutFieldset->addField(
            'layout_update_xml',
            'textarea',
            [
                'name'     => 'layout_update_xml',
                'label'    => __('Layout Update XML'),
                'style'    => 'height:24em;',
                'disabled' => $isElementDisabled
            ]
            );
        $this->_eventManager->dispatch('adminhtml_blog_category_edit_tab_main_prepare_form', ['form' => $form]);
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Design');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Design');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
