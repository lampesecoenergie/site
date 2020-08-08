<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Observer\Order\Track;

use Magento\Framework\Event\ObserverInterface;

class Shipment implements ObserverInterface
{
    /**
     * Amazon Logger
     * @var \Ced\Amazon\Helper\Logger
     */
    public $logger;

    /** @var \Ced\Amazon\Helper\Shipment  */
    public $shipment;

    /**
     * Shipment constructor.
     * @param \Ced\Amazon\Helper\Logger $logger
     * @param \Ced\Amazon\Helper\Shipment $shipment
     */
    public function __construct(
        \Ced\Amazon\Helper\Logger $logger,
        \Ced\Amazon\Helper\Shipment $shipment
    ) {
        $this->shipment = $shipment;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = null;
        try {
            /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
            $track = $observer->getEvent()->getTrack();
            /** @var \Magento\Sales\Model\Order\Shipment $shipment */
            $shipment = $track->getShipment();
            if (!empty($shipment)) {
                $data = $shipment->getData();
                $this->shipment->create($shipment);
            }
        } catch (\Exception $e) {
            $this->logger->critical(
                'Shipment create observer failed.',
                [
                    'exception' => $e->getMessage(),
                    'shipment' => $data,
                    'path' => __METHOD__
                ]
            );
        }
    }
}
