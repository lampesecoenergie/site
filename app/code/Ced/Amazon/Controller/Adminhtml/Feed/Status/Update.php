<?php
namespace Ced\Amazon\Controller\Adminhtml\Feed\Status;

/**
 * Class Update
 * @package Ced\Amazon\Controller\Adminhtml\Feed\Status
 */
class Update extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Amazon::feeds';

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /** @var \Ced\Amazon\Model\ResourceModel\Feed\CollectionFactory  */
    public $feed;

    /**
     * MassStatus constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Ced\Amazon\Model\ResourceModel\Feed\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Ced\Amazon\Model\ResourceModel\Feed\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->feed = $collectionFactory;
    }

    public function execute()
    {
        $filters = $this->getRequest()->getParam('filters');
        $status = $this->getRequest()->getParam('status');
        if (isset($filters, $status) && in_array($status, \Ced\Amazon\Model\Source\Feed\Status::STATUS_LIST)) {
            /** @var \Ced\Amazon\Model\ResourceModel\Feed\Collection $collection */
            $collection = $this->filter->getCollection($this->feed->create());

            /** @var \Ced\Amazon\Model\Feed $item */
            foreach ($collection as $item) {
                $item->setData(\Ced\Amazon\Model\Feed::COLUMN_STATUS, $status);
            }

            $collection->save();

            $this->messageManager
                ->addSuccessMessage(__('Status updated for %1 record(s).', $collection->getSize()));
        }

        return $this->_redirect('*/feeds/index');
    }
}
