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


namespace Mirasvit\Feed\Block\Adminhtml\Dynamic\Attribute\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form as WidgetForm;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mirasvit\Feed\Block\Adminhtml\Dynamic\Attribute\Edit\Renderer\Conditions;

class Form extends WidgetForm
{
    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Conditions
     */
    protected $conditionsElement;

    /**
     * @param Conditions  $conditionsElement
     * @param FormFactory $formFactory
     * @param Registry    $registry
     * @param Context     $context
     */
    public function __construct(
        Conditions $conditionsElement,
        FormFactory $formFactory,
        Registry $registry,
        Context $context
    ) {
        $this->conditionsElement = $conditionsElement;
        $this->formFactory = $formFactory;
        $this->registry = $registry;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $form = $this->formFactory->create()->setData([
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save', ['id' => $this->getRequest()->getParam('id')]),
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
        ]);

        $model = $this->getAttribute();

        $fieldset = $form->addFieldset('dynamic_category_form', ['legend' => __('General Information')]);

        if ($model->getId()) {
            $fieldset->addField('attribute_id', 'hidden', [
                'name'  => 'attribute_id',
                'value' => $model->getId(),
            ]);
        }

        $fieldset->addField('name', 'text', [
            'label'    => __('Name'),
            'required' => true,
            'name'     => 'name',
            'value'    => $model->getName(),
        ]);

        $fieldset->addField('code', 'text', [
            'label'    => __('Attribute Code'),
            'required' => true,
            'name'     => 'code',
            'value'    => $model->getCode(),
        ]);

        $fieldset->addElement($this->conditionsElement);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return \Mirasvit\Feed\Model\Dynamic\Attribute
     */
    public function getAttribute()
    {
        return $this->registry->registry('current_model');
    }
}
