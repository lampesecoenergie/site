<?php

namespace Cminds\AdminLogger\Block\Adminhtml\ActionHistory;

use Cminds\AdminLogger\Model\ResourceModel\AdminLogger\CollectionFactory;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\Data\CollectionFactory as DataCollectionFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\DataObjectFactory;

/**
 * Class Grid
 *
 * @package Cminds\AdminLogger\Block\Adminhtml\ActionHistory
 */
class Grid extends Extended
{
    /**
     * Collection factory object.
     *
     * @var CollectionFactory
     */
    private $dataCollectionFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Data
     */
    private $jsonHelper;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * Grid constructor.
     * @param Context               $context
     * @param Data                  $backendHelper
     * @param CollectionFactory     $collectionFactory
     * @param Registry              $registry
     * @param DataCollectionFactory $dataCollectionFactory
     * @param JsonHelper            $jsonHelper
     * @param DataObjectFactory     $dataObjectFactory
     * @param array                 $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $collectionFactory,
        Registry $registry,
        DataCollectionFactory $dataCollectionFactory,
        JsonHelper $jsonHelper,
        DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->registry = $registry;
        $this->dataCollectionFactory = $dataCollectionFactory;
        $this->jsonHelper = $jsonHelper;
        $this->dataObjectFactory = $dataObjectFactory;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize the Action History Details grid.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('adminlogger_actionhistory_details_view.phtml');
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $actionId = $this->registry->registry('cminds_adminlogger_action_id');
        $item = $this->collectionFactory->create()
            ->addFieldToFilter('id', $actionId)
            ->addFieldToSelect(
                [
                    'old_value',
                    'new_value'
                ]
            )->getFirstItem();

        $preparedCollection = $this->dataCollectionFactory->create();

        if ($item->getData('old_value') === null && $item->getData('new_value') === null) {
            $varienObject = $this->dataObjectFactory->create();
            $varienObject->setData(
                [
                    'name' => __('No records'),
                    'old_value' => __('No records'),
                    'new_value' => __('No records')
                ]
            );

            $preparedCollection->addItem($varienObject);
            $this->setCollection($preparedCollection);

            return parent::_prepareCollection();
        }

        $oldValue = $this->jsonHelper->jsonDecode($item->getData('old_value'));
        $newValue = $this->jsonHelper->jsonDecode($item->getData('new_value'));

        foreach ($oldValue as $key => $value) {
            $varienObject = $this->dataObjectFactory->create();
            $varienObject->setData(
                [
                    'name' => $key,
                    'old_value' => $value,
                    'new_value' => $newValue[$key]
                ]
            );

            $preparedCollection->addItem($varienObject);
        }

        $this->setCollection($preparedCollection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name'
            ]
        );
        $this->addColumn(
            'old_value',
            [
                'header' => __('Old Value'),
                'index' => 'old_value'
            ]
        );
        $this->addColumn(
            'new_value',
            [
                'header' => __('New Value'),
                'index' => 'new_value'
            ]
        );

        return parent::_prepareColumns();
    }
}
