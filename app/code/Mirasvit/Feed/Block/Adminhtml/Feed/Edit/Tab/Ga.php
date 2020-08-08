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

class Ga extends Form
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
     * {@inheritdoc}
     * @param FormFactory $formFactory
     * @param Registry    $registry
     * @param Context     $context
     */
    public function __construct(
        FormFactory $formFactory,
        Registry $registry,
        Context $context
    ) {
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
        $form->setFieldNameSuffix('feed');
        $this->setForm($form);

        $general = $form->addFieldset('general', ['legend' => __('Google Analytics')]);

        $general->addField('ga_source', 'text', [
            'name'  => 'ga_source',
            'label' => __('Campaign Source'),
            'value' => $model->getGaSource(),
            'note'  => __('Required. Referrer: google, newsletter4')
        ]);

        $general->addField('ga_medium', 'text', [
            'name'  => 'ga_medium',
            'label' => __('Campaign Medium'),
            'value' => $model->getGaMedium(),
            'note'  => __('Required. Marketing Medium: cpc, banner, email')
        ]);

        $general->addField('ga_name', 'text', [
            'name'  => 'ga_name',
            'label' => __('Campaign Name'),
            'value' => $model->getGaName(),
            'note'  => __('Required. Product, promo code, or slogan')
        ]);

        $general->addField('ga_term', 'text', [
            'name'  => 'ga_term',
            'label' => __('Campaign Term'),
            'value' => $model->getGaTerm(),
            'note'  => __('Identify the paid keywords')
        ]);

        $general->addField('ga_content', 'text', [
            'name'  => 'ga_content',
            'label' => __('Campaign Content'),
            'value' => $model->getGaContent(),
            'note'  => __('Use to differentiate ads')
        ]);

        return parent::_prepareForm();
    }
}
