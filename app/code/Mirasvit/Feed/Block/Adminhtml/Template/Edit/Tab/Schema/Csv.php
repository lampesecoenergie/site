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



namespace Mirasvit\Feed\Block\Adminhtml\Template\Edit\Tab\Schema;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mirasvit\Feed\Model\Config\Source\Delimiter as SourceDelimiter;
use Mirasvit\Feed\Model\Config\Source\Enclosure as SourceEnclosure;
use Mirasvit\Feed\Helper\Output as OutputHelper;

class Csv extends Form
{
    /**
     * @var OutputHelper
     */
    protected $outputHelper;

    /**
     * @var SourceDelimiter
     */
    protected $sourceDelimiter;

    /**
     * @var SourceEnclosure
     */
    protected $sourceEnclosure;

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
     * @param SourceDelimiter $sourceDelimiter
     * @param SourceEnclosure $sourceEnclosure
     * @param FormFactory     $formFactory
     * @param Registry        $registry
     * @param Context         $context
     */
    public function __construct(
        OutputHelper $outputHelper,
        SourceDelimiter $sourceDelimiter,
        SourceEnclosure $sourceEnclosure,
        FormFactory $formFactory,
        Registry $registry,
        Context $context
    ) {
        $this->outputHelper = $outputHelper;
        $this->sourceDelimiter = $sourceDelimiter;
        $this->sourceEnclosure = $sourceEnclosure;
        $this->formFactory = $formFactory;
        $this->registry = $registry;

        $this->_template = 'Mirasvit_Feed::template/edit/tab/schema/csv.phtml';

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $model = $this->getModel();
        $form = $this->formFactory->create();

        $this->setForm($form);

        $general = $form->addFieldset('content', ['legend' => __('Content Settings')]);

        $general->addField('delimiter', 'select', [
            'label'  => __('Fields Delimiter'),
            'name'   => 'csv[delimiter]',
            'value'  => $model->getData('csv_delimiter'),
            'values' => $this->sourceDelimiter->toOptionArray(),
        ]);

        $general->addField('enclosure', 'select', [
            'label'  => __('Fields enclosure'),
            'name'   => 'csv[enclosure]',
            'value'  => $model->getData('csv_enclosure'),
            'values' => $this->sourceEnclosure->toOptionArray(),
        ]);

        $general->addField('include_header', 'select', [
            'label'  => __('Include Columns Header'),
            'name'   => 'csv[include_header]',
            'value'  => $model->getData('csv_include_header'),
            'values' => [0 => __('No'), 1 => __('Yes')],
        ]);

        $general->addField('extra_header', 'textarea', [
            'label'    => __('Extra Header'),
            'required' => false,
            'name'     => 'csv[extra_header]',
            'value'    => $model->getData('csv_extra_header'),
        ]);

        return parent::_prepareForm();
    }

    /**
     * Return current template or feed model
     *
     * @return \Mirasvit\Feed\Model\AbstractTemplate
     */
    public function getModel()
    {
        return $this->registry->registry('current_model');
    }

    /**
     * @return array
     */
    public function getJsConfig()
    {
        return [
            "*" => [
                'Magento_Ui/js/core/app' => [
                    'components' => [
                        'schema_csv' => [
                            'component' => 'Mirasvit_Feed/js/template/edit/tab/schema/csv',
                            'config'    => [
                                'rows' => $this->getModel()->getCsvSchema(),
                            ]
                        ]
                    ],
                ]
            ]
        ];
    }
}
