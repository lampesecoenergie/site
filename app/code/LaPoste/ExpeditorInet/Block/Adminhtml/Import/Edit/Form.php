<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module
 * to newer versions in the future.
 *
 * @copyright 2017 La Poste
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace LaPoste\ExpeditorInet\Block\Adminhtml\Import\Edit;

use Magento\Backend\Block\Widget\Form\Generic;

/**
 * Import form.
 *
 * @author Smile (http://www.smile.fr)
 */
class Form extends Generic
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/import'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Import Settings')]);

        $fieldset->addField(
            'track_title',
            'text',
            [
                'name' => 'track_title',
                'label' => __('Track Title'),
                'title' => __('Track Title'),
            ]
        );

        $fieldset->addField(
            'import_file',
            'file',
            [
                'name' => 'import_file',
                'label' => __('Import File'),
                'title' => __('Import File'),
                'required' => true,
                'class' => 'input-file',
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
