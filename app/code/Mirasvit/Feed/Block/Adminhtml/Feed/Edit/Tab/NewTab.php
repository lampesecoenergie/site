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


namespace Mirasvit\Feed\Block\Adminhtml\Feed\Edit\Tab;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mirasvit\Feed\Model\Config\Source\Template as SourceTemplate;

class NewTab extends Form
{
    /**
     * @var SourceTemplate
     */
    protected $sourceTemplate;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * {@inheritdoc}
     * @param SourceTemplate $sourceTemplate
     * @param FormFactory    $formFactory
     * @param Registry       $registry
     * @param Context        $context
     */
    public function __construct(
        SourceTemplate $sourceTemplate,
        FormFactory $formFactory,
        Registry $registry,
        Context $context
    ) {
        $this->sourceTemplate = $sourceTemplate;
        $this->formFactory = $formFactory;
        $this->registry = $registry;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'continue_button',
            $this->getLayout()->createBlock('\Magento\Backend\Block\Widget\Button')
                ->setData([
                    'label'          => __('Continue'),
                    'class'          => 'primary',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => [
                                'event'     => 'saveAndContinueEdit',
                                'target'    => '#edit_form',
                                'eventData' => ['action' => ['args' => ['auto_apply' => 1]]],
                            ],
                        ],
                    ],
                ])
        );

        return parent::_prepareLayout();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $model = $this->registry->registry('current_model');
        $form = $this->formFactory->create();
        $form->setFieldNameSuffix('feed');
        $this->setForm($form);

        $general = $form->addFieldset('general', ['legend' => __('Settings')]);

        $general->addField('template_id', 'select', [
            'label'    => __('Template'),
            'required' => false,
            'name'     => 'template_id',
            'value'    => $model->getType(),
            'values'   => $this->sourceTemplate->toOptionArray(),
        ]);

        $general->addField('continue_button', 'note', [
            'text' => $this->getChildHtml('continue_button'),
        ]);

        return parent::_prepareForm();
    }
}
