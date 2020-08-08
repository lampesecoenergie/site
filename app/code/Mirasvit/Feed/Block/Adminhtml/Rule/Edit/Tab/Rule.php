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



namespace Mirasvit\Feed\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Rule\Block\Conditions as RuleConditions;

class Rule extends Form
{
    /**
     * @var Fieldset
     */
    protected $fieldset;

    /**
     * @var RuleConditions
     */
    protected $conditions;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Context
     */
    protected $context;

    /**
     * {@inheritdoc}
     * @param Fieldset       $fieldset
     * @param RuleConditions $conditions
     * @param FormFactory    $formFactory
     * @param Registry       $registry
     * @param Context        $context
     */
    public function __construct(
        Fieldset $fieldset,
        RuleConditions $conditions,
        FormFactory $formFactory,
        Registry $registry,
        Context $context
    ) {
        $this->fieldset = $fieldset;
        $this->conditions = $conditions;
        $this->formFactory = $formFactory;

        $this->registry = $registry;
        $this->context = $context;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $model = $this->registry->registry('current_model');

        $form = $this->formFactory->create();

        $form->setHtmlIdPrefix('rule_');

        $renderer = $this->fieldset
            ->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl(
                '*/rule/newConditionHtml/form/rule_conditions_fieldset',
                ['rule_type' => $model->getType()]
            ));

        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            ['legend' => __('Filters (leave blank for select all products)')]
        )->setRenderer($renderer);

        $fieldset->addField('conditions', 'text', [
            'name'     => 'conditions',
            'label'    => __('Filters'),
            'title'    => __('Filters'),
            'required' => true,
        ])->setRule($model)
            ->setRenderer($this->conditions);

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
