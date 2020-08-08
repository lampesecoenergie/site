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


namespace Mirasvit\Feed\Block\Adminhtml\Feed;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Grid\Extended as ExtendedGrid;
use Magento\Backend\Helper\Data as BackendHelper;
use Mirasvit\Feed\Model\ResourceModel\Feed\CollectionFactory as FeedCollectionFactory;

class Grid extends ExtendedGrid
{
    /**
     * @var FeedCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param FeedCollectionFactory $collectionFactory
     * @param Context               $context
     * @param BackendHelper         $backendHelper
     */
    public function __construct(
        FeedCollectionFactory $collectionFactory,
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

        $this->setId('feed_feed_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * {@inheritdoc}
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
        $this->addColumn('feed_id', [
            'header' => __('ID'),
            'align'  => 'right',
            'width'  => '50px',
            'index'  => 'feed_id',
            'type'   => 'number'
        ]);

        $this->addColumn('name', [
            'header' => __('Name'),
            'align'  => 'left',
            'index'  => 'name',
        ]);

        $this->addColumn('feed_type', [
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

        $this->addColumn('file', [
            'header'   => __('File'),
            'align'    => 'left',
            'index'    => 'type',
            'renderer' => '\Mirasvit\Feed\Block\Adminhtml\Feed\Renderer\Link',
        ]);

        $this->addColumn('last_generated', [
            'header' => __('Last Generated At'),
            'align'  => 'left',
            'type'   => 'datetime',
            'index'  => 'generated_at',
        ]);

        $this->addColumn('feed_status', [
            'header'   => __('Status'),
            'align'    => 'left',
            'filter'   => false,
            'sortable' => false,
            'width'    => '150px',
            'renderer' => '\Mirasvit\Feed\Block\Adminhtml\Feed\Renderer\Status',
        ]);

        $this->addColumn('action', [
            'header'    => __('Action'),
            'width'     => '100',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => [
                [
                    'caption' => __('Edit'),
                    'url'     => ['base' => '*/*/edit'],
                    'field'   => 'id',
                ],
                [
                    'caption' => __('Duplicate'),
                    'url'     => ['base' => '*/*/duplicate'],
                    'field'   => 'id',
                ],
                [
                    'caption' => __('Remove'),
                    'url'     => ['base' => '*/*/delete'],
                    'field'   => 'id',
                    'confirm' => __('Are you sure?'),
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
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }
}
