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
use Mirasvit\Feed\Model\Config;
use Mirasvit\Feed\Model\Config\Source\ImportEntities;
use Mirasvit\Feed\Model\Config\Source\Template as SourceTemplate;
use Mirasvit\Feed\Model\Config\Source\Rule as SourceRule;
use Mirasvit\Feed\Model\Config\Source\Dynamic\Attribute as SourceAttribute;
use Mirasvit\Feed\Model\Config\Source\Dynamic\Category as SourceCategory;
use Mirasvit\Feed\Model\Config\Source\Dynamic\Variable as SourceVariable;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Import extends Form
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ImportEntities
     */
    private $importEntities;

    /**
     * @var SourceTemplate
     */
    private $sourceTemplate;

    /**
     * @var SourceRule
     */
    private $sourceRule;

    /**
     * @var SourceAttribute
     */
    private $sourceAttribute;

    /**
     * @var SourceCategory
     */
    private $sourceCategory;

    /**
     * @var SourceVariable
     */
    private $sourceVariable;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Config $config,
        FormFactory $formFactory,
        Registry $registry,
        ImportEntities $importEntities,
        SourceTemplate $sourceTemplate,
        SourceRule $sourceRule,
        SourceAttribute $sourceAttribute,
        SourceCategory $sourceCategory,
        SourceVariable $sourceVariable
    ) {
        $this->config = $config;
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->importEntities = $importEntities;
        $this->sourceTemplate = $sourceTemplate;
        $this->sourceRule = $sourceRule;
        $this->sourceAttribute = $sourceAttribute;
        $this->sourceCategory = $sourceCategory;
        $this->sourceVariable = $sourceVariable;

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

        $fieldSet = $form->addFieldset(
            'import_fieldset', [
                'legend' => __('Select Data to Import'),
            ]
        );

        $sourceImport = $fieldSet->addField(
            'import_data',
            'select',
            [
                'name'   => 'import_data',
                'label'  => __('Select Data to Import'),
                'values' => $this->importEntities->toOptionArray(),
            ]);

        $templates = $fieldSet->addField(
            'import_template',
            'multiselect',
            [
                'label'  => __('Templates'),
                'title'  => __('Templates'),
                'name'   => 'template',
                'values' => $this->sourceTemplate->toOptionArray(true),
                'note'   => $this->prettyPath('Templates import path:', $this->config->getTemplatePath()),
            ]);

        $rules = $fieldSet->addField(
            'import_rule',
            'multiselect',
            [
                'label'  => __('Filters'),
                'title'  => __('Filters'),
                'name'   => 'rule',
                'values' => $this->sourceRule->toOptionArray(true),
                'note'   => $this->prettyPath('Filters import path:', $this->config->getRulePath()),
            ]);

        $dynamicAttributes = $fieldSet->addField(
            'import_dynamic_attribute',
            'multiselect',
            [
                'label'  => __('Dynamic Attributes'),
                'title'  => __('Dynamic Attributes'),
                'name'   => 'dynamic_attribute',
                'values' => $this->sourceAttribute->toOptionArray(true),
                'note'   => $this->prettyPath('Dynamic Attributes import path:',
                    $this->config->getDynamicAttributePath()),
            ]);

        $dynamicCategories = $fieldSet->addField(
            'import_dynamic_category',
            'multiselect',
            [
                'label'  => __('Dynamic Categories'),
                'title'  => __('Dynamic Categories'),
                'name'   => 'dynamic_category',
                'values' => $this->sourceCategory->toOptionArray(true),
                'note'   => $this->prettyPath('Dynamic Categories import path:',
                    $this->config->getDynamicCategoryPath()),
            ]);

        $dynamicVariables = $fieldSet->addField(
            'import_dynamic_variable',
            'multiselect',
            [
                'label'  => __('Dynamic Variables'),
                'title'  => __('Dynamic Variables'),
                'name'   => 'dynamic_variable',
                'values' => $this->sourceVariable->toOptionArray(true),
                'note'   => $this->prettyPath('Dynamic Variables import path:',
                    $this->config->getDynamicVariablePath()),
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

        $importUrl = $this->getUrl('*/*/importAction');
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData([
            'label'   => 'Import Data',
            'class'   => 'secondary',
            'onclick' => "require('uiRegistry').get('import_processor').process('$importUrl')",
        ]);

        $fieldSet->addField('import_button', 'note', [
            'name' => 'import_button',
            'text' => $button->toHtml(),
        ]);

        return parent::_prepareForm();
    }

    /**
     * @param string $prefix
     * @param string $path
     * @return string
     */
    private function prettyPath($prefix, $path)
    {
        $rootPath = $this->config->getRootPath();

        $path = str_replace($rootPath, '&lt;Magento install dir&gt/', $path);

        return "<i>$prefix</i></br><pre>$path</pre>";
    }
}
