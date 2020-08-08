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
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Shipping\Model\ShipmentNotifier;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Ui\Component\MassAction\Filter;
use Exception;

/**
 * Class MassShipment
 */
class MassShipment extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'MondialRelay_Shipping::label';

    const SHIPPING_IS_NEW_PROCESS = 'shipping_is_new';

    /**
     * @var LabelGenerator $labelGenerator
     */
    protected $labelGenerator;

    /**
     * @var OrderRepositoryInterface $orderRepository
     */
    protected $orderRepository;

    /**
     * @var ShipmentRepositoryInterface $shipmentRepository
     */
    protected $shipmentRepository;

    /**
     * @var ConvertOrder $convertOrder
     */
    protected $convertOrder;

    /**
     * @var ShipmentNotifier $shipmentNotifier
     */
    protected $shipmentNotifier;

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
     * @param OrderRepositoryInterface $orderRepository
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param ConvertOrder $convertOrder
     * @param ShipmentNotifier $shipmentNotifier
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param FileFactory $fileFactory
     * @param Label $label
     */
    public function __construct(
        Context $context,
        LabelGenerator $labelGenerator,
        OrderRepositoryInterface $orderRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        ConvertOrder $convertOrder,
        ShipmentNotifier $shipmentNotifier,
        Filter $filter,
        CollectionFactory $collectionFactory,
        FileFactory $fileFactory,
        Label $label
    ) {
        parent::__construct($context);

        $this->filter             = $filter;
        $this->collectionFactory  = $collectionFactory;
        $this->labelGenerator     = $labelGenerator;
        $this->orderRepository    = $orderRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->convertOrder       = $convertOrder;
        $this->shipmentNotifier   = $shipmentNotifier;
        $this->fileFactory        = $fileFactory;
        $this->label              = $label;
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
                if (!$shipments->count()) {
                    $shipment = $this->convertOrder->toShipment($order);

                    /** @var Item $orderItem */
                    foreach ($order->getAllItems() as $orderItem) {
                        if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                            continue;
                        }

                        $qty = $orderItem->getQtyToShip();
                        $shipmentItem = $this->convertOrder->itemToShipmentItem($orderItem)->setQty($qty);

                        $shipment->addItem($shipmentItem);
                    }

                    $shipment->register();
                    $shipment->getOrder()->setIsInProcess(true);
                    $shipment->setData(self::SHIPPING_IS_NEW_PROCESS, true);
                    $shipments = [$shipment];
                }

                foreach ($shipments as $shipment) {
                    $label = $shipment->getShippingLabel();
                    if (!$label) {
                        $this->labelGenerator->create($shipment, $this->getRequest());
                        $this->shipmentRepository->save($shipment);
                        $label = $shipment->getShippingLabel();
                    }

                    $labelsContent[] = $label;

                    if ($shipment->getData(self::SHIPPING_IS_NEW_PROCESS) === true) {
                        $this->orderRepository->save($order);
                        $this->shipmentNotifier->notify($shipment);
                    }
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
