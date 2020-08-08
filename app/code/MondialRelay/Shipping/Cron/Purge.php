<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Cron;

use MondialRelay\Shipping\Model\Label;
use MondialRelay\Shipping\Helper\Data as ShippingHelper;
use MondialRelay\Shipping\Model\Carrier\MondialRelay;
use Magento\Sales\Api\ShipmentTrackRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class Purge
 */
class Purge
{
    /**
     * @var ShipmentTrackRepositoryInterface $shipmentTrackRepository
     */
    protected $shipmentTrackRepository;

    /**
     * @var Label $label
     */
    protected $label;

    /**
     * @var ShippingHelper $shippingHelper
     */
    protected $shippingHelper;

    /**
     * @var SearchCriteriaBuilder $searchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var DateTime $dateTime
     */
    protected $dateTime;

    /**
     * @param ShippingHelper $shippingHelper
     * @param Label $label
     * @param ShipmentTrackRepositoryInterface $shipmentTrackRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DateTime $dateTime
     */
    public function __construct(
        ShippingHelper $shippingHelper,
        Label $label,
        ShipmentTrackRepositoryInterface $shipmentTrackRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DateTime $dateTime
    ) {
        $this->shippingHelper          = $shippingHelper;
        $this->shipmentTrackRepository = $shipmentTrackRepository;
        $this->label                   = $label;
        $this->searchCriteriaBuilder   = $searchCriteriaBuilder;
        $this->dateTime                = $dateTime;
    }

    /**
     * Execute
     *
     * @return $this
     */
    public function execute()
    {
        $days = $this->shippingHelper->getDeleteLabelAfter();

        if (!$days) {
            return $this;
        }

        $dateTo   = $this->dateTime->date(null, $this->dateTime->date() . ' - ' . intval($days) . ' days');
        $dateFrom = $this->dateTime->date(null, $this->dateTime->date() . ' - ' . (intval($days) + 5) . ' days');

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ShipmentTrackInterface::CREATED_AT, $dateFrom, 'gteq')
            ->addFilter(ShipmentTrackInterface::CREATED_AT, $dateTo, 'lteq')
            ->addFilter(ShipmentTrackInterface::CARRIER_CODE, MondialRelay::SHIPPING_CARRIER_CODE, 'eq')
            ->create();

        $tracks = $this->shipmentTrackRepository->getList($searchCriteria);

        /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
        foreach ($tracks as $track) {
            $this->label->deleteLabel($track->getParentId());
        }

        return $this;
    }
}
