<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Model\ReturnLabel;

use MondialRelay\Shipping\Helper\Data as ShippingHelper;
use Magento\Sales\Model\ResourceModel\Order\Address\Collection;
use Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class DataProvider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var Collection $collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface $dataPersistor
     */
    protected $dataPersistor;

    /**
     * @var array $loadedData
     */
    protected $loadedData;

    /**
     * @var ShippingHelper $shippingHelper
     */
    protected $shippingHelper;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $orderAddressCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param ShippingHelper $shippingHelper
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $orderAddressCollectionFactory,
        DataPersistorInterface $dataPersistor,
        ShippingHelper $shippingHelper,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->meta           = $this->prepareMeta($this->meta);
        $this->collection     = $orderAddressCollectionFactory->create();
        $this->dataPersistor  = $dataPersistor;
        $this->shippingHelper = $shippingHelper;
    }

    /**
     * Prepares Meta
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var $address \Magento\Sales\Model\Order\Address */
        foreach ($items as $address) {
            $order = $address->getOrder();
            $address->addData($this->shippingHelper->getRecipientReturnAddress($order->getStoreId()));
            $address->setData('street_1', $address->getStreetLine(1));
            $address->setData('street_2', $address->getStreetLine(2));
            $address->setData('weight', round($order->getWeight(), 2));
            $this->loadedData[$address->getId()] = $address->getData();
        }

        $data = $this->dataPersistor->get('address');
        if (!empty($data)) {
            $address = $this->collection->getNewEmptyItem();
            $address->setData($data);
            $this->loadedData[$address->getId()] = $address->getData();
            $this->dataPersistor->clear('address');
        }

        return $this->loadedData;
    }
}
