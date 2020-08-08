<?php

namespace Acyba\GLS\Controller\Adminhtml\Export;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use \Magento\Backend\App\Action\Context;
use \Magento\Ui\Component\MassAction\Filter;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use \Magento\Framework\Controller\ResultFactory;
use Acyba\GLS\Model\Export;

class MassExport extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var \Acyba\GLS\Model\Export $export
     */
    protected $export;

    protected $resultRedirect;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Acyba\GLS\Model\Export $export
     */
    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory, Export $export)
    {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->export = $export;
    }

    /**
     * Export selected orders
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $this->export->export($collection, false);

        $this->messageManager->addSuccessMessage(__('Orders have been exported'));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath($this->redirectUrl);
    }
}
