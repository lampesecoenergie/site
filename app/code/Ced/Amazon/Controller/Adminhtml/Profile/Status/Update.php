<?php
namespace Ced\Amazon\Controller\Adminhtml\Profile\Status;

/**
 * Class Update
 * @package Ced\Amazon\Controller\Adminhtml\Profile\Status
 */
class Update extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * @var \Ced\Amazon\Model\ResourceModel\Profile\CollectionFactory
     */
    public $profile;

    /**
     * MassStatus constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Ced\Amazon\Model\ResourceModel\Profile\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Ced\Amazon\Model\ResourceModel\Profile\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->profile = $collectionFactory;
    }

    public function execute()
    {
        $filters = $this->getRequest()->getParam('filters');
        $status = $this->getRequest()->getParam('status', 0);
        if (isset($filters)) {
            $status = $status == 1 ? $status : 0;
            /** @var \Ced\Amazon\Model\ResourceModel\Profile\Collection $collection */
            $collection = $this->filter->getCollection($this->profile->create());

            /** @var \Ced\Amazon\Api\Data\ProfileInterface $item */
            foreach ($collection as $item) {
                $item->setProfileSatus($status);
            }

            $collection->save();

            $this->messageManager
                ->addSuccessMessage(__('Status updated of %1 record(s).', count($collection->getSize())));
        }

        return $this->_redirect('*/*/index');
    }
}
