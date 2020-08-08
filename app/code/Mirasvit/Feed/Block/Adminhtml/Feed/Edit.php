<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Block\Adminhtml\Feed;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;
use Mirasvit\Feed\Helper\Data as FeedHelper;

class Edit extends Container
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var FeedHelper
     */
    protected $dataHelper;

    /**
     * {@inheritdoc}
     * @param Registry   $registry
     * @param Context    $context
     * @param FeedHelper $dataHelper
     */
    public function __construct(
        Registry $registry,
        Context $context,
        FeedHelper $dataHelper
    ) {
        $this->registry = $registry;
        $this->dataHelper = $dataHelper;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_objectId = 'feed_id';
        $this->_blockGroup = 'Mirasvit_feed';
        $this->_controller = 'adminhtml_feed';

        $this->buttonList->remove('save');

        if ($this->getModel()->getId() > 0) {
            $previewUrl = $this->dataHelper->getFeedPreviewUrl($this->getModel());

            $deliveryUrl = $this->dataHelper->getFeedDeliverUrl($this->getModel());

            if ($this->getModel()->getFtp()) {
                $this->buttonList->add('delivery', [
                    'label'   => __('Delivery Feed'),
                    'class'   => 'delivery',
                    'onclick' => 'setLocation(\'' . $deliveryUrl . '\')'
                ], -100);
            }

            $this->buttonList->add('preview', [
                'label'          => __('Preview Feed'),
                'class'          => 'preview',
                'data_attribute' => [
                    'mage-init' => [
                        'feedPreview' => [
                            'url' => $previewUrl
                        ],
                    ]
                ]
            ], -100);

            $this->getToolbar()->addChild(
                'save-split-button',
                'Magento\Backend\Block\Widget\Button\SplitButton',
                [
                    'id'           => 'save-split-button',
                    'label'        => __('Save'),
                    'class_name'   => 'Magento\Backend\Block\Widget\Button\SplitButton',
                    'button_class' => 'widget-button-update',
                    'options'      => [
                        [
                            'id'             => 'save-button',
                            'label'          => __('Save'),
                            'default'        => true,
                            'data_attribute' => [
                                'mage-init' => [
                                    'button' => [
                                        'event'  => 'saveAndContinueEdit',
                                        'target' => '#edit_form'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'id'             => 'save-continue-button',
                            'label'          => __('Save & Close'),
                            'data_attribute' => [
                                'mage-init' => [
                                    'button' => [
                                        'event'  => 'save',
                                        'target' => '#edit_form'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            );

            $this->buttonList->add('Generate', [
                'label'   => __('Generate'),
                'class'   => 'generate',
                'onclick' => "require('uiRegistry').get('feed_export').generate()",
            ], 100);
        }
    }

    /**
     * Return feed model
     *
     * @return \Mirasvit\Feed\Model\Feed
     */
    public function getModel()
    {
        return $this->registry->registry('current_model');
    }
}
