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


namespace Mirasvit\Feed\Block\Adminhtml\Template\Edit\Tab;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mirasvit\Feed\Model\Config\Source\Type as SourceType;

class General extends Form
{
    /**
     * @var SourceType
     */
    protected $sourceType;

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
     * @param SourceType  $sourceType
     * @param FormFactory $formFactory
     * @param Registry    $registry
     * @param Context     $context
     */
    public function __construct(
        SourceType $sourceType,
        FormFactory $formFactory,
        Registry $registry,
        Context $context
    ) {
        $this->sourceType = $sourceType;
        $this->formFactory = $formFactory;
        $this->registry = $registry;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        $model = $this->registry->registry('current_model');
        $form = $this->formFactory->create();
        $this->setForm($form);

        $general = $form->addFieldset('general', ['legend' => __('General Information')]);

        if ($model->getId()) {
            $general->addField('template_id', 'hidden', [
                'name'  => 'template_id',
                'value' => $model->getId(),
            ]);
        }

        $general->addField('name', 'text', [
            'label'    => __('Name'),
            'required' => true,
            'name'     => 'name',
            'value'    => $model->getName(),
        ]);

        $general->addField('type', 'select', [
            'label'    => __('File Type'),
            'required' => true,
            'name'     => 'type',
            'value'    => $model->getType(),
            'values'   => $this->sourceType->toOptionArray(),
        ]);

        return parent::_prepareForm();
    }
}
