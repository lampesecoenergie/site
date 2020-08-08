<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module
 * to newer versions in the future.
 *
 * @copyright 2017 La Poste
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace LaPoste\ExpeditorInet\Controller\Adminhtml\Import;

use LaPoste\ExpeditorInet\Helper\Config as ConfigHelper;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\CsvFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Shipping\Model\Order\TrackFactory;
use Psr\Log\LoggerInterface;
use \Magento\Framework\ObjectManagerInterface;

/**
 * Shipment import controller.
 *
 * @author Smile (http://www.smile.fr)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Import extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'LaPoste_ExpeditorInet::import_shipments';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var CsvFactory
     */
    protected $csvFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var TrackFactory
     */
    protected $trackFactory;

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var ShipmentSender
     */
    protected $shipmentSender;
	
	protected $_objectManager;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param ConfigHelper $configHelper
     * @param CsvFactory $csvFactory
     * @param OrderFactory $orderFactory
     * @param ShipmentFactory $shipmentFactory
     * @param TrackFactory $trackFactory
     * @param TransactionFactory $transactionFactory
     * @param ShipmentSender $shipmentSender
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        ConfigHelper $configHelper,
        CsvFactory $csvFactory,
        OrderFactory $orderFactory,
        ShipmentFactory $shipmentFactory,
        TrackFactory $trackFactory,
        TransactionFactory $transactionFactory,
        ShipmentSender $shipmentSender,
		ObjectManagerInterface $objectmanager
    ) {
        $this->configHelper = $configHelper;
        $this->logger = $logger;
        $this->csvFactory = $csvFactory;
        $this->orderFactory = $orderFactory;
        $this->shipmentFactory = $shipmentFactory;
        $this->trackFactory = $trackFactory;
        $this->transactionFactory = $transactionFactory;
        $this->shipmentSender = $shipmentSender;
		$this->_objectManager = $objectmanager;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function execute()
    {
        $fileInfo = $this->getRequest()->getFiles('import_file');

        if ($this->getRequest()->isPost() && isset($fileInfo['tmp_name'])) {
            try {
                $fileName = $fileInfo['tmp_name'];
                $trackTitle = $this->getRequest()->getPost('track_title');
                $this->importShipmentsFromCsv($fileName, $trackTitle);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(__('An error occurred while creating the shipments.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Invalid file upload attempt.'));
        }

        $this->_redirect('*/*');
    }

    /**
     * Import the file.
     *
     * @param string $fileName
     * @param string $trackTitle
     */
    protected function importShipmentsFromCsv($fileName, $trackTitle)
    {
        ini_set('auto_detect_line_endings', true);

        // Prepare the CSV file
        /** @var \Magento\Framework\File\Csv $csvObject */
        $csvObject = $this->csvFactory->create();
        $csvObject->setDelimiter($this->configHelper->getImportFieldDelimiter());
        $csvObject->setEnclosure($this->configHelper->getImportFieldEnclosure());
        $csvData = $csvObject->getData($fileName);

        // Default track title
        if (!$trackTitle) {
            $trackTitle = $this->configHelper->getImportDefaultTrackingTitle();
        }

        // $k is line number, $v is line content array
        foreach ($csvData as $k => $v) {
            // End of file has more than one empty lines
            if (count($v) <= 1 && !strlen($v[0])) {
                continue;
            }

            // Check that the number of fields is not lower than expected
            if (count($v) < 2) {
                $this->messageManager->addErrorMessage(__('Line #%1: invalid format.', $k));
                continue;
            }

            // Get fields content
             $orderIncrementId = $v[0];
			//echo "<br>";
             $trackNumber = $v[1];
			//exit;

            try {
                // Try to load the order
                /** @var \Magento\Sales\Model\Order $order */
                $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
                if (!$order->getId()) {
                    $this->messageManager->addErrorMessage(__('Order #%1 does not exist.', $orderIncrementId));
                    continue;
                }

                // Create the shipment
                $shipment = $this->createShipment($order, $trackNumber, $trackTitle);
                $message = 'Shipment #%1 created for order #%2, with tracking number "%3".';
                $message = __($message, $shipment->getIncrementId(), $orderIncrementId, $trackNumber);
                $this->messageManager->addSuccessMessage($message);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $message = __('Shipment creation failed for order #%1.', $orderIncrementId);
                $this->messageManager->addErrorMessage($message);
            }
        }
    }

    /**
     * Create new shipment for order.
     *
     * @param Order $order
     * @param string $trackNumber
     * @param string $trackTitle
     * @return \Magento\Sales\Model\Order\Shipment
     * @throws LocalizedException
     */
    protected function createShipment(Order $order, $trackNumber, $trackTitle)
    {
        if (!$order->canShip()) {
            $message = __('Order #%1 can not be shipped or has already been shipped.', $order->getIncrementId());
            throw new LocalizedException($message);
        }

        $sendEmail = $this->configHelper->getImportSendEmail();
        $comment = $this->configHelper->getImportShipmentComment();
        $includeCommentInEmail = $this->configHelper->getImportIncludeCommentInEmail();
        $carrierCode = $this->configHelper->getImportCarrierCode();

        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
		
if ($order->canShip()) {		
$convertOrder = $this->_objectManager->create('Magento\Sales\Model\Convert\Order');
$shipment = $convertOrder->toShipment($order);
 
// Loop through order items
foreach ($order->getAllItems() AS $orderItem) {
// Check if order item is virtual or has quantity to ship
if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
continue;
}
 
$qtyShipped = $orderItem->getQtyToShip();
 
// Create shipment item with qty
$shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
 
// Add shipment item to shipment
$shipment->addItem($shipmentItem);
}
		
		
		
        //$shipment = $this->shipmentFactory->create($order);
        $shipment->register();
}
        // Add track
        $this->addTrackToShipment($shipment, $carrierCode, $trackNumber, $trackTitle);

        // Include comment
        if ($comment) {
            $notifyCustomer = $sendEmail && $includeCommentInEmail;
            $shipment->addComment($comment, $notifyCustomer);
            $shipment->setCustomerNoteNotify($notifyCustomer);
            $shipment->setCustomerNote($comment);
        }

        // Save the shipment and the order
        $order->setIsInProcess(true);
        $this->transactionFactory->create()
            ->addObject($shipment)
            ->addObject($order)
            ->save();

        // Send email
        if ($sendEmail) {
            $this->shipmentSender->send($shipment);
        }

        return $shipment;
    }

    /**
     * Add a track to the shipment.
     *
     * @param Shipment $shipment
     * @param string $carrierCode
     * @param string $trackNumber
     * @param string $trackTitle
     * @return \Magento\Shipping\Model\Order\Track
     */
    protected function addTrackToShipment(Shipment $shipment, $carrierCode, $trackNumber, $trackTitle)
    {
        /** @var \Magento\Shipping\Model\Order\Track $track */
        $track = $this->trackFactory->create();
        $track->setCarrierCode($carrierCode)
            ->setTrackNumber($trackNumber)
            ->setTitle($trackTitle);
        $shipment->addTrack($track);

        return $track;
    }
}
