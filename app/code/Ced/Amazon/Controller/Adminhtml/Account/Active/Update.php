<?php
namespace Ced\Amazon\Controller\Adminhtml\Account\Active;

/**
 * Class Update
 * @package Ced\Amazon\Controller\Adminhtml\Account\Status
 */
class Update extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Amazon::account';

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /** @var \Ced\Amazon\Model\ResourceModel\Account\CollectionFactory  */
    public $profile;

    /**
     * MassStatus constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Ced\Amazon\Model\ResourceModel\Account\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Ced\Amazon\Model\ResourceModel\Account\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->profile = $collectionFactory;
    }

    public function execute()
    {
        $filters = $this->getRequest()->getParam('filters');
        $active = $this->getRequest()->getParam('active', 0);
        if (isset($filters)) {
            $active = $active == 1 ? $active : 0;
            /** @var \Ced\Amazon\Model\ResourceModel\Account\Collection $collection */
            $collection = $this->filter->getCollection($this->profile->create());

            /** @var \Ced\Amazon\Model\Account $item */
            foreach ($collection as $item) {
                $item->setData(\Ced\Amazon\Model\Account::COLUMN_ACTIVE, $active);
            }

            $collection->save();

            $this->messageManager
                ->addSuccessMessage(__('Activated/Deactivated %1 record(s).', $collection->getSize()));
        }

        return $this->_redirect('*/*/index');
    }
}
