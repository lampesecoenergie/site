<?php
namespace Ced\Amazon\Controller\Adminhtml\Queue\Status;

/**
 * Class Update
 * @package Ced\Amazon\Controller\Adminhtml\Queue\Status
 */
class Update extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Amazon::queue';

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /** @var \Ced\Amazon\Model\ResourceModel\Queue\CollectionFactory  */
    public $queue;

    /**
     * MassStatus constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Ced\Amazon\Model\ResourceModel\Queue\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Ced\Amazon\Model\ResourceModel\Queue\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->queue = $collectionFactory;
    }

    public function execute()
    {
        $filters = $this->getRequest()->getParam('filters');
        $status = $this->getRequest()->getParam('status');
        if (isset($filters, $status) && in_array($status, \Ced\Amazon\Model\Source\Queue\Status::STATUS_LIST)) {
            /** @var \Ced\Amazon\Model\ResourceModel\Queue\Collection $collection */
            $collection = $this->filter->getCollection($this->queue->create());

            /** @var \Ced\Amazon\Model\Queue $item */
            foreach ($collection as $item) {
                $item->setData(\Ced\Amazon\Model\Queue::COLUMN_STATUS, $status);
            }

            $collection->save();

            $this->messageManager
                ->addSuccessMessage(__('Status updated for %1 record(s).', $collection->getSize()));
        }

        return $this->_redirect('*/*/index');
    }
}
