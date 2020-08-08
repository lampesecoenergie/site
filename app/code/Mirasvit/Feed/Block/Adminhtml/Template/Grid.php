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



namespace Mirasvit\Feed\Block\Adminhtml\Template;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Grid\Extended as ExtendedGrid;
use Magento\Backend\Helper\Data as BackendHelper;
use Mirasvit\Feed\Model\ResourceModel\Template\CollectionFactory as TemplateCollectionFactory;

class Grid extends ExtendedGrid
{
    /**
     * @var TemplateCollectionFactory
     */
    protected $collectionFactory;

    /**
     * {@inheritdoc}
     * @param TemplateCollectionFactory $collectionFactory
     * @param Context                   $context
     * @param BackendHelper             $backendHelper
     */
    public function __construct(
        TemplateCollectionFactory $collectionFactory,
        Context $context,
        BackendHelper $backendHelper
    ) {
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context, $backendHelper);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('feed_template_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->collectionFactory->create();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', [
            'header' => __('ID'),
            'align'  => 'right',
            'width'  => '50px',
            'index'  => 'template_id',
        ]);

        $this->addColumn('name', [
            'header' => __('Name'),
            'align'  => 'left',
            'index'  => 'name',
        ]);

        $this->addColumn('type', [
            'header'  => __('Type'),
            'align'   => 'left',
            'index'   => 'type',
            'type'    => 'options',
            'options' => [
                'csv' => __('CSV'),
                'txt' => __('TXT'),
                'xml' => __('XML'),
            ],
        ]);

        $this->addColumn('action', [
            'header'    => __('Action'),
            'width'     => '100',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => [
                [
                    'caption' => __('Export Template'),
                    'url'     => ['base' => '*/*/export'],
                    'field'   => 'id',
                ],
                [
                    'caption' => __('Edit'),
                    'url'     => ['base' => '*/*/edit'],
                    'field'   => 'id',
                ],
                [
                    'caption' => __('Remove'),
                    'url'     => ['base' => '*/*/delete'],
                    'field'   => 'id',
                ],
            ],
            'filter'    => false,
            'sortable'  => false,
            'is_system' => true,
        ]);

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('template');

        $this->getMassactionBlock()->addItem('delete', [
            'label'   => __('Delete'),
            'url'     => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Are you sure?'),
        ]);

        $this->getMassactionBlock()->addItem('export', [
            'label' => __('Export'),
            'url'   => $this->getUrl('*/*/massExport'),
        ]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }
}
