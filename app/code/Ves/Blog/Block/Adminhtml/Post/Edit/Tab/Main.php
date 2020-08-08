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
namespace Ves\Blog\Block\Adminhtml\Post\Edit\Tab;

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    protected $_dateTime;

    protected $_category;

    protected $_thumbnailType;
    protected $_videoType;

    protected $_user;

    /**
     * @param \Magento\Backend\Block\Template\Context
     * @param \Magento\Framework\Registry
     * @param \Magento\Framework\Data\FormFactory
     * @param \Magento\Store\Model\System\Store
     * @param \Magento\Framework\Stdlib\DateTime\DateTime
     * @param \Ves\Blog\Model\Category
     * @param \Ves\Blog\Model\Config\Source\ThumbnailType
     * @param \Ves\Blog\Model\Config\Source\VideoType
     * @param \Ves\Blog\Model\Config\Source\User
     * @param array
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Ves\Blog\Model\Category $category,
        \Ves\Blog\Model\Config\Source\ThumbnailType $thumbnailType,
        \Ves\Blog\Model\Config\Source\VideoType $videoType,
        \Ves\Blog\Model\Config\Source\User $user,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_dateTime = $dateTime;
        $this->_category = $category;
        $this->_thumbnailType = $thumbnailType;
        $this->_videoType = $videoType;
        $this->_user = $user;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('current_post');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Ves_Blog::post_save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('post_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Post Information')]);

        if ($model->getId()) {
            $fieldset->addField('post_id', 'hidden', ['name' => 'post_id']);
        }

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Post Title'),
                'title' => __('Post Title'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'identifier',
            'text',
            [
                'name' => 'identifier',
                'label' => __('Identifier'),
                'title' => __('Identifier'),
                'class' => 'validate-identifier',
                'required' => true,
                'note' => __('Relative to Web Site Base URL'),
                'disabled' => $isElementDisabled
            ]
        ); 

        $fieldset->addField(
            'image_type',
            'select',
            [
                'label' => __('Image Types'),
                'title' => __('Image Types'),
                'name' => 'image_type',
                'options' => $this->_thumbnailType->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => 'Shown on post page',
                'after_element_html' => '
                    <script>
                        require(["jquery"], function($){
                            $( document ).ready(function() {
                                $("#post_image_type").on("change", function(){
                                    var val = $(this).val();
                                    if(val == "1"){
                                        $("#post_image").parents(".admin__field").show();
                                        $("#post_image_video_type").parents(".admin__field").hide();
                                        $("#post_image_video_id").parents(".admin__field").hide();
                                    }else{
                                        $("#post_image").parents(".admin__field").hide();
                                        $("#post_image_video_type").parents(".admin__field").show();
                                        $("#post_image_video_id").parents(".admin__field").show();
                                    }
                                }).change();
                            });
                        });
                    </script>
                '
            ]
        );

        $fieldset->addField(
            'image_video_type',
            'select',
            [
                'label' => __('Video Types'),
                'title' => __('Video Types'),
                'name' => 'image_video_type',
                'options' => $this->_videoType->toOptionArray(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'image_video_id',
            'text',
            [
                'name' => 'image_video_id',
                'label' => __('Video ID'),
                'title' => __('Video ID'),
                'after_element_html' => 'For Examples:<br/> 1. Youtube<br/> Link: https://www.youtube.com/watch?v=BBvsB5PcitQ<br/> VideoID: <strong>BBvsB5PcitQ</strong><br/>2. Vimeo<br/> Link: https://vimeo.com/145947876<br/> VideoID: <strong>145947876</strong>'
            ]
            );

        $fieldset->addField(
            'image',
            'image',
            [
                'name' => 'image',
                'label' => __('Image'),
                'title' => __('Image'),
                'disabled' => $isElementDisabled
            ]
            );

        $fieldset->addField(
            'thumbnail_type',
            'select',
            [
                'label' => __('Thumbnail Types'),
                'title' => __('Thumbnail Types'),
                'name' => 'thumbnail_type',
                'options' => $this->_thumbnailType->toOptionArray(),
                'disabled' => $isElementDisabled,
                'note' => 'Shown on post page',
                'after_element_html' => '
                    <script>
                        require(["jquery"], function($){
                            $( document ).ready(function() {
                                $("#post_thumbnail_type").on("change", function(){
                                    var val = $(this).val();
                                    if(val == "1"){
                                        $("#post_thumbnail").parents(".admin__field").show();
                                        $("#post_thumbnail_video_type").parents(".admin__field").hide();
                                        $("#post_thumbnail_video_id").parents(".admin__field").hide();
                                    }else{
                                        $("#post_thumbnail").parents(".admin__field").hide();
                                        $("#post_thumbnail_video_type").parents(".admin__field").show();
                                        $("#post_thumbnail_video_id").parents(".admin__field").show();
                                    }
                                }).change();
                            });
                        });
                    </script>
                '
            ]
        );

        $fieldset->addField(
            'thumbnail_video_type',
            'select',
            [
                'label' => __('Video Types'),
                'title' => __('Video Types'),
                'name' => 'thumbnail_video_type',
                'options' => $this->_videoType->toOptionArray(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'thumbnail_video_id',
            'text',
            [
                'name' => 'thumbnail_video_id',
                'label' => __('Video ID'),
                'title' => __('Video ID'),
                'after_element_html' => 'For Examples:<br/> 1. Youtube<br/> Link: https://www.youtube.com/watch?v=BBvsB5PcitQ<br/> VideoID: <strong>BBvsB5PcitQ</strong><br/>2. Vimeo<br/> Link: https://vimeo.com/145947876<br/> VideoID: <strong>145947876</strong>'
            ]
            );

        $fieldset->addField(
            'thumbnail',
            'image',
            [
                'name' => 'thumbnail',
                'label' => __('Image'),
                'title' => __('Image'),
                'disabled' => $isElementDisabled
            ]
            );

        $field = $fieldset->addField(
                'categories',
                'multiselect',
                [
                    'name' => 'categories[]',
                    'label' => __('Category'),
                    'title' => __('Category'),
                    'values' => $this->_category->getCollection()->toOptionArray(),
                    'disabled' => $isElementDisabled,
                    'style' => 'width: 200px;'
                ]
            );

        $fieldset->addField(
            'tags',
            'text',
            [
                'name' => 'tags',
                'label' => __('Tags'),
                'title' => __('Tags'),
                'note' => __('Comma-separated.'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'hits',
            'text',
            [
                'name' => 'hits',
                'label' => __('Hits'),
                'title' => __('Hits'),
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'user_id',
            'select',
            [
                'label' => __('Author'),
                'title' => __('Author'),
                'name' => 'user_id',
                'options' => $this->_user->toOptionArray(),
                'disabled' => $isElementDisabled
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField( 'creation_time', 
            'date', 
            [ 
                'label' => __('Creation Time'),
                'title' => __('Creation Time'),
                'name' => 'creation_time',
                'date_format' => $dateFormat
            ]
        );

        $fieldset->addField(
            'enable_comment',
            'select',
            [
                'label'    => __('Enable Comments'),
                'title'    => __('Enable Comments'),
                'name'     => 'enable_comment',
                'note'     => __('Disabling will close the post to new comments'),
                'options'  => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );


        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->_systemStore->getStoreValuesForForm(false, true),
                    'disabled' => $isElementDisabled
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Post Status'),
                'title' => __('Post Status'),
                'name' => 'is_active',
                'options' => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );
        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $this->_eventManager->dispatch('adminhtml_blog_post_edit_tab_main_prepare_form', ['form' => $form]);
        $data = $model->getData();
        if(!isset($data['creation_time']) || (isset($data['creation_time']) && $data['creation_time'] == '')){
            //$data['creation_time'] = $this->_dateTime->gmtDate();
        }
        if(!isset($data['hits'])){
            $data['hits'] = 0;
            $data['enable_comment'] = 1;
        }
        $form->setValues($data);
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
        return __('Post Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Post Information');
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
