<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Ui\DataProvider\Order;

use MondialRelay\Shipping\Api\Data\ShippingDataInterface;
use MondialRelay\Shipping\Model\Carrier\MondialRelay;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Zend_Db_Expr;
use Zend_Db_Select;

/**
 * Class PriceDataProvider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory $collection
     */
    protected $collection;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     */
    protected $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     */
    protected $addFilterStrategies;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $collectionFactory->create();

        $this->collection->addFilterToMap('store_id', 'main_table.' . OrderInterface::STORE_ID);
        $this->collection->addFilterToMap('created_at', 'main_table.' . OrderInterface::CREATED_AT);
        $this->collection->addFilterToMap('increment_id', 'main_table.' . OrderInterface::INCREMENT_ID);
        $this->collection->addFilterToMap('shipped_at', 's.' . ShipmentInterface::CREATED_AT);
        $this->collection->addFilterToMap('shipment_status', 's.' . ShipmentInterface::SHIPMENT_STATUS);

        $this->collection->addFieldToFilter(
            'shipping_method',
            ['like' => MondialRelay::SHIPPING_CARRIER_CODE . '_%']
        );
        $this->collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $this->collection->getSelect()->columns(
            [
                'order_id'             => 'main_table.' . OrderInterface::ENTITY_ID,
                'store_id'             => 'main_table.' . OrderInterface::STORE_ID,
                'created_at'           => 'main_table.' . OrderInterface::CREATED_AT,
                'increment_id'         => 'main_table.' . OrderInterface::INCREMENT_ID,
                'status'               => 'main_table.' . OrderInterface::STATUS,
                'shipping_description' => 'main_table.' . OrderInterface::SHIPPING_DESCRIPTION,
                'base_shipping_amount' => 'main_table.' . OrderInterface::BASE_SHIPPING_AMOUNT,
                'weight'               => 'main_table.' . OrderInterface::WEIGHT,
                'entity_id'            => new Zend_Db_Expr(
                    'IF(
                        s.' . ShipmentInterface::ENTITY_ID . ',
                        CONCAT(main_table.' . OrderInterface::ENTITY_ID . ', "-", s.' . ShipmentInterface::ENTITY_ID . '),
                        main_table.' . OrderInterface::ENTITY_ID . '
                    )'
                ),
                'mondialrelay_packaging_weight' => 'main_table.' . ShippingDataInterface::MONDIAL_RELAY_PACKAGING_WEIGHT,
            ]
        );
        $this->collection->getSelect()->joinLeft(
            ['s' => 'sales_shipment'],
            's.order_id = main_table.entity_id',
            [
                'shipment_id'     => 's.' . ShipmentInterface::ENTITY_ID,
                'shipped_at'      => 's.' . ShipmentInterface::CREATED_AT,
                'shipment_status' => 's.' . ShipmentInterface::SHIPMENT_STATUS,
                'has_label'       => new Zend_Db_Expr(
                    'IF(s.' . ShipmentInterface::SHIPPING_LABEL . ' IS NULL, 0, 1)'
                ),
                'weight'          => new Zend_Db_Expr(
                    'IF(
                        s.' . ShipmentInterface::TOTAL_WEIGHT . ' IS NULL,
                        main_table.' . OrderInterface::WEIGHT . ',
                        s.' . ShipmentInterface::TOTAL_WEIGHT . '
                    )'
                )
            ]
        );

        $this->addFieldStrategies  = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }
}
