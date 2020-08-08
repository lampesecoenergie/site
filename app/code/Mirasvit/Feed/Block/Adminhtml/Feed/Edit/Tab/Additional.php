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
use Mirasvit\Feed\Model\Config\Source\EmailEvent as SourceEmailEvent;

class Additional extends Form
{
    /**
     * @var SourceEmailEvent
     */
    protected $sourceEmailEvent;

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
     * @param SourceEmailEvent $sourceEmailEvent
     * @param FormFactory      $formFactory
     * @param Registry         $registry
     * @param Context          $context
     */
    public function __construct(
        SourceEmailEvent $sourceEmailEvent,
        FormFactory $formFactory,
        Registry $registry,
        Context $context
    ) {
        $this->sourceEmailEvent = $sourceEmailEvent;
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

        $email = $form->addFieldset('email_fieldset', ['legend' => __('Email Notifications')]);

        $email->addField('notification_emails', 'text', [
            'name'  => 'notification_emails',
            'label' => __('Email'),
            'value' => $model->getNotificationEmails(),
            'note'  => __('Separate emails by commas')
        ]);

        $email->addField('notification_events', 'multiselect', [
            'name'   => 'notification_events',
            'label'  => __('Notification Events'),
            'value'  => $model->getNotificationEvents(),
            'values' => $this->sourceEmailEvent->toOptionArray(),
        ]);

        $report = $form->addFieldset('report_fieldset', ['legend' => __('Reports Configuration')]);

        $report->addField('report_enabled', 'select', [
            'name'     => 'report_enabled',
            'label'    => __('Enable Reports'),
            'required' => false,
            'values'   => [0 => __('No'), 1 => __('Yes')],
            'value'    => $model->getReportEnabled(),
            'note'     => __('If enabled, extension append two special arguments
                (ff=, fp=) to product url for track clicks and orders'),
        ]);

        return parent::_prepareForm();
    }
}
