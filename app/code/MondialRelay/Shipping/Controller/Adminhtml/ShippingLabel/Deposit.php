<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2017 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Controller\Adminhtml\ShippingLabel;

use MondialRelay\Shipping\Helper\Data as ShippingHelper;
use MondialRelay\Shipping\Model\Deposit\Pdf;
use MondialRelay\Shipping\Model\Carrier\MondialRelay;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class Deposit
 */
class Deposit extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'MondialRelay_Shipping::label';

    /**
     * @var ShippingHelper $shippingHelper
     */
    protected $shippingHelper;

    /**
     * @var ShipmentRepositoryInterface $shipmentRepository
     */
    protected $shipmentRepository;

    /**
     * @var OrderAddressRepositoryInterface $orderAddressRepository
     */
    protected $orderAddressRepository;

    /**
     * @var Pdf $pdf
     */
    protected $pdf;

    /**
     * @var FileFactory $fileFactory
     */
    protected $fileFactory;

    /**
     * @var Filter $filter
     */
    protected $filter;

    /**
     * @var CollectionFactory $collectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param ShippingHelper $shippingHelper
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param OrderAddressRepositoryInterface $orderAddressRepository
     * @param FileFactory $fileFactory
     * @param Pdf $pdf
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        ShippingHelper $shippingHelper,
        ShipmentRepositoryInterface $shipmentRepository,
        OrderAddressRepositoryInterface $orderAddressRepository,
        FileFactory $fileFactory,
        Pdf $pdf,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);

        $this->shippingHelper         = $shippingHelper;
        $this->shipmentRepository     = $shipmentRepository;
        $this->orderAddressRepository = $orderAddressRepository;
        $this->fileFactory            = $fileFactory;
        $this->pdf                    = $pdf;
        $this->filter                 = $filter;
        $this->collectionFactory      = $collectionFactory;
    }

    /**
     * Create deposit file
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        $data = [
            'company' => [
                'name'   => $this->shippingHelper->getCompanyName(),
            ],
            'shipments' => [],
        ];

        $totalWeight = 0;
        $totalCount  = 0;

        /** @var Order $order */
        foreach ($collection as $order) {
            if (!$order->getEntityId()) {
                continue;
            }

            $shipments = $order->getShipmentsCollection();

            foreach ($shipments as $shipment) {
                $shippingAddress = $this->orderAddressRepository->get($shipment->getShippingAddressId());

                /** @var Track $track */
                foreach ($shipment->getTracks() as $track) {
                    if ($track->getCarrierCode() !== MondialRelay::SHIPPING_CARRIER_CODE) {
                        continue;
                    }

                    $data['shipments'][] = [
                        'increment_id' => $shipment->getIncrementId(),
                        'name'         => $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname(),
                        'tracking'     => $track->getTrackNumber(),
                        'postcode'     => $shippingAddress->getPostcode(),
                        'country'      => $shippingAddress->getCountryId(),
                        'weight'       => floatval($shipment->getTotalWeight()),
                    ];

                    $totalWeight += $shipment->getTotalWeight();
                    $totalCount++;
                }
            }
        }

        $data['summary'] = [
            'total_shipment' => $totalCount,
            'total_weight'   => $totalWeight,
        ];

        $this->fileFactory->create($this->pdf->getFileName(), $this->pdf->getFile($data), DirectoryList::TMP);
    }
}
