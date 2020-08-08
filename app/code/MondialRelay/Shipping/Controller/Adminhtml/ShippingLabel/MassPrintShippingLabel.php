<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Controller\Adminhtml\ShippingLabel;

use MondialRelay\Shipping\Model\Label;
use MondialRelay\Shipping\Model\Config\Source\Status;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Ui\Component\MassAction\Filter;
use Exception;

/**
 * Class MassPrintShippingLabel
 */
class MassPrintShippingLabel extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'MondialRelay_Shipping::label';

    /**
     * @var LabelGenerator $labelGenerator
     */
    protected $labelGenerator;

    /**
     * @var Filter $filter
     */
    protected $filter;

    /**
     * @var CollectionFactory $collectionFactory
     */
    protected $collectionFactory;

    /**
     * @var FileFactory $fileFactory
     */
    protected $fileFactory;

    /**
     * @var Label $label
     */
    protected $label;

    /**
     * @param Context $context
     * @param LabelGenerator $labelGenerator
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param FileFactory $fileFactory
     * @param Label $label
     */
    public function __construct(
        Context $context,
        LabelGenerator $labelGenerator,
        Filter $filter,
        CollectionFactory $collectionFactory,
        FileFactory $fileFactory,
        Label $label
    ) {
        parent::__construct($context);

        $this->filter            = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->labelGenerator    = $labelGenerator;
        $this->fileFactory       = $fileFactory;
        $this->label             = $label;
    }

    /**
     * Mass create shipment and label
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $ids = $this->_request->getParam(Filter::SELECTED_PARAM);

        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collection->getSelect()->order(
            'FIELD(' . OrderInterface::ENTITY_ID . ', ' . join(', ', $ids) . ')'
        );

        $labelsContent = [];

        /** @var Order $order */
        foreach ($collection as $order) {
            try {
                $shipments = $order->getShipmentsCollection();

                foreach ($shipments as $shipment) {
                    $label = $shipment->getShippingLabel();
                    if (!$label) {
                        continue;
                    }

                    $labelsContent[] = $label;
                }
            } catch (Exception $e) {
                $labelsContent[] = $this->label->generateErrorLabel(
                    [
                        __('Order %1', $order->getIncrementId()),
                        $e->getMessage(),
                    ]
                );
            }
        }

        if (!empty($labelsContent)) {
            $outputPdf = $this->labelGenerator->combineLabelsPdf($labelsContent);
            return $this->fileFactory->create(
                'ShippingLabels.pdf',
                $outputPdf->render(),
                DirectoryList::VAR_DIR,
                'application/pdf'
            );
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
