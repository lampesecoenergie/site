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
use Mirasvit\Core\Api\CronHelperInterface;
use Mirasvit\Feed\Model\Config\Source\Day as SourceDay;
use Mirasvit\Feed\Model\Config\Source\Time as SourceTime;

class Cron extends Form
{
    /**
     * @var SourceDay
     */
    protected $sourceDay;

    /**
     * @var SourceTime
     */
    protected $sourceTime;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CronHelperInterface
     */
    protected $cronHelper;


    /**
     * {@inheritdoc}
     *
     * @param SourceDay   $sourceDay
     * @param SourceTime  $sourceTime
     * @param FormFactory $formFactory
     * @param Registry    $registry
     * @param CronHelperInterface  $cronHelper
     * @param Context     $context
     */
    public function __construct(
        SourceDay $sourceDay,
        SourceTime $sourceTime,
        FormFactory $formFactory,
        Registry $registry,
        CronHelperInterface $cronHelper,
        Context $context
    ) {
        $this->sourceDay = $sourceDay;
        $this->sourceTime = $sourceTime;
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->cronHelper = $cronHelper;

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

        $general = $form->addFieldset('general', ['legend' => __('Scheduled Task')]);

        $general->addField('cron', 'select', [
            'name'   => 'cron',
            'label'  => __('Enabled'),
            'value'  => $model->getCron(),
            'values' => [0 => __('No'), 1 => __('Yes')],
            'note'   => __(
                'If enabled, extension will generate feed by schedule.
                 To generate feed by schedule, magento cron must be configured.'
            )
        ]);

        $general->addField('cron_day', 'multiselect', [
            'label'    => __('Days of the week'),
            'required' => false,
            'name'     => 'cron_day',
            'values'   => $this->sourceDay->toOptionArray(),
            'value'    => $model->getCronDay(),
        ]);

        $general->addField('cron_time', 'multiselect', [
            'label'    => __('Time of the day'),
            'required' => false,
            'name'     => 'cron_time',
            'values'   => $this->sourceTime->toOptionArray(),
            'value'    => $model->getCronTime(),
        ]);

        list($status, $message) = $this->cronHelper->checkCronStatus(false, false);

        if (!$status) {
            $general->addField('cron_job_status', 'note', [
                'label'    => __('Cronjob status'),
                'required' => false,
                'name'     => 'cron_day',
                'note'     => $message,
            ]);
        }

        return parent::_prepareForm();
    }
}
