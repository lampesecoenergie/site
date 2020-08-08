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
namespace Ves\Blog\Block\Adminhtml\Comment\Edit\Tab;

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
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    protected $_postFactory;

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
     * @param \Magento\Cms\Model\Wysiwyg\Config
     * @param \Magento\Catalog\Model\Product
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
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Ves\Blog\Model\Post $postFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_dateTime = $dateTime;
        $this->_category = $category;
        $this->_thumbnailType = $thumbnailType;
        $this->_videoType = $videoType;
        $this->_user = $user;
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_postFactory = $postFactory;
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
        $model = $this->_coreRegistry->registry('current_comment');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Ves_Blog::comment_save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('comment_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Comment Information')]);

        if ($model->getId()) {
            $fieldset->addField('comment_id', 'hidden', ['name' => 'comment_id']);
        }

        $post = $this->_postFactory->load($model->getPostId());

        $fieldset->addField(
            'post_name',
            'note',
            [
                'label' => __('Post'),
                'name' => 'post_name',
                'text' => $post->getTitle()
            ]
        );

        $fieldset->addField(
            'user_name',
            'text',
            [
                'name' => 'user_name',
                'label' => __('User Name'),
                'title' => __('User Name'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'user_email',
            'text',
            [
                'name' => 'user_email',
                'label' => __('User Email'),
                'title' => __('User Email'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );


        $wysiwygConfig = $this->_wysiwygConfig->getConfig(['tab_id' => $this->getTabId().time()]);
        $contentField = $fieldset->addField(
            'content',
            'editor',
            [
                'label' => __('Content'),
                'title' => __('Content'),
                'name' => 'content',
                'style' => 'height:20em;',
                'disabled' => $isElementDisabled,
                'config' => $wysiwygConfig
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
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'is_active',
                'options' => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );
        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $this->_eventManager->dispatch('adminhtml_blog_comment_edit_tab_main_prepare_form', ['form' => $form]);
        $data = $model->getData();
        if(!isset($data['creation_time']) || (isset($data['creation_time']) && $data['creation_time'] == '')){
            //$data['creation_time'] = $this->_dateTime->gmtDate();
        }
        if(!isset($data['hits'])){
            $data['hits'] = 0;
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
