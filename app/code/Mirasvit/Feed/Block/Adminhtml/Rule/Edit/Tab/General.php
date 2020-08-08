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



namespace Mirasvit\Feed\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Mirasvit\Feed\Model\ResourceModel\Feed\CollectionFactory as FeedCollectionFactory;

class General extends Form
{
    /**
     * @var FeedCollectionFactory
     */
    protected $feedCollectionFactory;

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
     * @param FeedCollectionFactory $feedCollectionFactory
     * @param FormFactory           $formFactory
     * @param Registry              $registry
     * @param Context               $context
     */
    public function __construct(
        FeedCollectionFactory $feedCollectionFactory,
        FormFactory $formFactory,
        Registry $registry,
        Context $context
    ) {
        $this->feedCollectionFactory = $feedCollectionFactory;
        $this->formFactory = $formFactory;
        $this->registry = $registry;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareForm()
    {
        /** @var \Mirasvit\Feed\Model\Rule $model */
        $model = $this->registry->registry('current_model');
        $form = $this->formFactory->create();
        $form->setFieldNameSuffix('data');
        $this->setForm($form);

        $general = $form->addFieldset('general', ['legend' => __('General Information')]);

        if ($model->getId()) {
            $general->addField('rule_id', 'hidden', [
                'name'  => 'rule_id',
                'value' => $model->getId(),
            ]);
        }

        $general->addField('name', 'text', [
            'label'    => __('Name'),
            'required' => true,
            'name'     => 'name',
            'value'    => $model->getName(),
        ]);

        $general->addField('is_active', 'select', [
            'label'    => __('Is Active'),
            'required' => true,
            'name'     => 'is_active',
            'value'    => $model->getIsActive(),
            'values'   => [0 => __('No'), 1 => __('Yes')],
        ]);

        $general->addField('feeds', 'checkboxes', [
            'label'    => __('Feeds'),
            'required' => false,
            'name'     => 'feed_ids[]',
            'values'   => $this->feedCollectionFactory->create()->toOptionArray(),
            'checked'  => $model->getFeedIds(),
        ]);

        return parent::_prepareForm();
    }
}
