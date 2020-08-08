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
use Mirasvit\Feed\Model\Config\Source\FtpProtocol as SourceFtpProtocol;

class Ftp extends Form
{
    /**
     * @var SourceFtpProtocol
     */
    protected $sourceFtpProtocol;

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
     * @param SourceFtpProtocol $sourceFtpProtocol
     * @param FormFactory       $formFactory
     * @param Registry          $registry
     * @param Context           $context
     */
    public function __construct(
        SourceFtpProtocol $sourceFtpProtocol,
        FormFactory $formFactory,
        Registry $registry,
        Context $context
    ) {
        $this->sourceFtpProtocol = $sourceFtpProtocol;
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

        $general = $form->addFieldset('general', ['legend' => __('FTP Settings')]);

        $general->addField('ftp', 'select', [
            'name'     => 'ftp',
            'label'    => __('Enabled'),
            'required' => false,
            'values'   => [0 => __('No'), 1 => __('Yes')],
            'value'    => $model->getFtp(),
        ]);

        $general->addField('ftp_protocol', 'select', [
            'name'     => 'ftp_protocol',
            'label'    => __('Protocol'),
            'required' => true,
            'values'   => $this->sourceFtpProtocol->toOptionArray(),
            'value'    => $model->getFtpProtocol(),
        ]);

        $general->addField('ftp_host', 'text', [
            'name'     => 'ftp_host',
            'label'    => __('Host Name'),
            'required' => false,
            'value'    => $model->getFtpHost(),
        ]);
        $general->addField('ftp_user', 'text', [
            'name'     => 'ftp_user',
            'label'    => __('User Name'),
            'required' => false,
            'value'    => $model->getFtpUser(),
        ]);
        $general->addField('ftp_password', 'password', [
            'name'     => 'ftp_password',
            'label'    => __('Password'),
            'required' => false,
            'value'    => $model->getFtpPassword(),
        ]);
        $general->addField('ftp_path', 'text', [
            'name'     => 'ftp_path',
            'label'    => __('Path'),
            'required' => false,
            'value'    => $model->getFtpPath(),
        ]);

        $general->addField('ftp_passive_mode', 'select', [
            'name'     => 'ftp_passive_mode',
            'label'    => __('Passive mode'),
            'required' => false,
            'values'   => [0 => __('No'), 1 => __('Yes')],
            'value'    => $model->getFtpPassiveMode(),
        ]);


        /** @var \Magento\Backend\Block\Widget\Button $button */
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData([
            'label'          => '<i class="fa fa-exchange fa-fw"></i> '.__('Test Connection'),
            'class'          => 'secondary',
            'data_attribute' => [
                'mage-init' => [
                    'ftpValidator' => [
                        'url' => $this->getUrl('feed/feed/validateFtp')
                    ],
                ]
            ]
        ]);

        $general->addField('ftp_check_connection', 'note', [
            'name' => 'ftp_check_connection',
            'text' => $button->toHtml(),
        ]);

        return parent::_prepareForm();
    }
}
