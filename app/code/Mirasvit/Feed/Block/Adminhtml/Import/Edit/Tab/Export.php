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



namespace Mirasvit\Feed\Block\Adminhtml\Import\Edit\Tab;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mirasvit\Feed\Model\Config\Source\ImportEntities;
use Mirasvit\Feed\Model\TemplateFactory;
use Mirasvit\Feed\Model\RuleFactory;
use Mirasvit\Feed\Model\Dynamic\AttributeFactory;
use Mirasvit\Feed\Model\Dynamic\CategoryFactory;
use Mirasvit\Feed\Model\Dynamic\VariableFactory;


class Export extends Form
{
    /**
     * {@inheritdoc}
     * @param ImportEntities   $importEntities
     * @param FormFactory      $formFactory
     * @param Registry         $registry
     * @param Context          $context
     * @param TemplateFactory  $templateFactory
     * @param RuleFactory      $ruleFactory
     * @param AttributeFactory $attributeFactory
     * @param CategoryFactory  $categoryFactory
     * @param VariableFactory  $variableFactory
     */
    public function __construct(
        ImportEntities $importEntities,
        FormFactory $formFactory,
        Registry $registry,
        Context $context,
        TemplateFactory $templateFactory,
        RuleFactory $ruleFactory,
        AttributeFactory $attributeFactory,
        CategoryFactory $categoryFactory,
        VariableFactory $variableFactory
    ) {
        $this->importEntities = $importEntities;
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->templateFactory = $templateFactory;
        $this->ruleFactory = $ruleFactory;
        $this->attributeFactory = $attributeFactory;
        $this->categoryFactory = $categoryFactory;
        $this->variableFactory = $variableFactory;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $form = $this->formFactory->create();
        $this->setForm($form);

        $templateCollection = $this->templateFactory->create()->getCollection();
        $ruleCollection = $this->ruleFactory->create()->getCollection();
        $attributeCollection = $this->attributeFactory->create()->getCollection();
        $categoryCollection = $this->categoryFactory->create()->getCollection();
        $variableCollection = $this->variableFactory->create()->getCollection();

        $fieldSet = $form->addFieldset(
            'export_fieldset', [
                'legend' => __('Select Data to Export'),
            ]
        );

        $sourceImport = $fieldSet->addField(
            'export_data',
            'select',
            [
                'name'   => 'export_data',
                'label'  => __('Select Data to Export'),
                'values' => $this->importEntities->toOptionArray(),
            ]);


        $templates = $fieldSet->addField(
            'export_template',
            'multiselect',
            [
                'label'  => __('Templates'),
                'title'  => __('Templates'),
                'name'   => 'template',
                'values' => $templateCollection->toOptionArray(),
            ]);

        $rules = $fieldSet->addField(
            'export_rule',
            'multiselect',
            [
                'label'  => __('Filters'),
                'title'  => __('Filters'),
                'name'   => 'rule',
                'values' => $ruleCollection->toOptionArray(),
            ]);

        $dynamicAttributes = $fieldSet->addField(
            'export_dynamic_attribute',
            'multiselect',
            [
                'label'  => __('Dynamic Attributes'),
                'title'  => __('Dynamic Attributes'),
                'name'   => 'dynamic_attribute',
                'values' => $attributeCollection->toOptionArray(true),
            ]);

        $dynamicCategories = $fieldSet->addField(
            'export_dynamic_category',
            'multiselect',
            [
                'label'  => __('Dynamic Categories'),
                'title'  => __('Dynamic Categories'),
                'name'   => 'dynamic_category',
                'values' => $categoryCollection->toOptionArray(true),
            ]);

        $dynamicVariables = $fieldSet->addField(
            'export_dynamic_variable',
            'multiselect',
            [
                'label'  => __('Dynamic Variable'),
                'title'  => __('Dynamic Variable'),
                'name'   => 'dynamic_variable',
                'values' => $variableCollection->toOptionArray(true),
            ]);

        $this->setChild('form_after',
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
                ->addFieldMap($sourceImport->getHtmlId(), $sourceImport->getName())
                ->addFieldMap($templates->getHtmlId(), $templates->getName())
                ->addFieldMap($rules->getHtmlId(), $rules->getName())
                ->addFieldMap($dynamicAttributes->getHtmlId(), $dynamicAttributes->getName())
                ->addFieldMap($dynamicCategories->getHtmlId(), $dynamicCategories->getName())
                ->addFieldMap($dynamicVariables->getHtmlId(), $dynamicVariables->getName())
                ->addFieldDependence($templates->getName(), $sourceImport->getName(), 'template')
                ->addFieldDependence($rules->getName(), $sourceImport->getName(), 'rule')
                ->addFieldDependence($dynamicAttributes->getName(), $sourceImport->getName(), 'dynamic_attribute')
                ->addFieldDependence($dynamicCategories->getName(), $sourceImport->getName(), 'dynamic_category')
                ->addFieldDependence($dynamicVariables->getName(), $sourceImport->getName(), 'dynamic_variable')
        );

        $exportUrl = $this->getUrl('*/*/exportAction');
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData([
            'label'   => 'Export Data',
            'class'   => 'secondary',
            'onclick' => "require('uiRegistry').get('import_processor').process('$exportUrl')",
        ]);

        $fieldSet->addField('export_button', 'note', [
            'name' => 'export_button',
            'text' => $button->toHtml(),
        ]);

        return parent::_prepareForm();

    }
}
