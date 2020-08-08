<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fooman\OrderManager\Plugin;

use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection as TrackCollection;

class GridCollection
{
    /**
     * @var TrackCollection
     */
    private $trackCollection;

    public function __construct(
        TrackCollection $trackCollection
    ) {
        $this->trackCollection = $trackCollection;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $subject
     * @param                                                          $result
     */
    public function afterLoadWithFilter(
        \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $subject,
        $result
    ) {
        $data = [];
        $this->trackCollection->addFieldToFilter('order_id', ['in' => $result->getAllIds()]);

        foreach ($this->trackCollection as $item) {
            if (empty($data[$item->getOrderId()])) {
                $data[$item->getOrderId()] = [
                    'carrier' => [],
                    'number' => [],
                ];
            }
            $data[$item->getOrderId()]['carrier'][] = $item->getCarrierCode();
            $data[$item->getOrderId()]['number'][] = $item->getTrackNumber();
        }

        foreach ($result->getItems() as $item) {
            if (empty($data[$item->getEntityId()])) {
                continue;
            }

            $item->setTrackingCarrier(implode(',', $data[$item->getEntityId()]['carrier']));
            $item->setTrackingNumber(implode(',', $data[$item->getEntityId()]['number']));
        }
    }
}
