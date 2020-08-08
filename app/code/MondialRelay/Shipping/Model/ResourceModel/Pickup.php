<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2018 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Model\ResourceModel;

use MondialRelay\Shipping\Api\Data\ShippingDataInterface;
use MondialRelay\Shipping\Model\Config\Source\Code;
use MondialRelay\Shipping\Helper\Data as ShippingHelper;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order\Address;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Pickup
 */
class Pickup extends AbstractDb
{
    /**
     * Prefix for resources that will be used in this resource model
     *
     * @var string
     */
    protected $connectionName = 'checkout';

    /**
     * @var Code $code
     */
    protected $code;

    /**
     * @var ShippingHelper $shippingHelper
     */
    protected $shippingHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Code $code
     * @param ShippingHelper $shippingHelper
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        Code $code,
        ShippingHelper $shippingHelper,
        $connectionName = null
    ) {
        $this->code = $code;
        $this->shippingHelper = $shippingHelper;
        parent::__construct($context, $connectionName);
    }

    /**
     * Model initialization
     *
     * @return void
     * @phpcs:disable
     */
    protected function _construct()
    {
        $this->_init('quote_mondialrelay_pickup', 'quote_id');
    }

    /**
     * Save pickup data for quote
     *
     * @param string   $cartId
     * @param string   $pickupId
     * @param string   $countryId
     * @param string   $code
     * @param string[] $address
     *
     * @return bool
     * @throws LocalizedException
     */
    public function savePickup($cartId, $pickupId, $countryId, $code, $address)
    {
        $codes = $this->code->toArray();

        if (!isset($codes[$code])) {
            throw new LocalizedException(__('Code unknown'));
        }

        $countries = $this->shippingHelper->getConfig('pickup/countries');

        if (!in_array($countryId, $countries)) {
            throw new LocalizedException(__('Country not available'));
        }

        $connection = $this->getConnection();

        $data = [
            'quote_id'   => $cartId,
            'pickup_id'  => $pickupId,
            'country_id' => $countryId,
            'code'       => $code,
        ];

        $data = array_merge($address, $data);

        $connection->insertOnDuplicate(
            $this->getMainTable(),
            $data,
            array_keys($data)
        );

        return true;
    }

    /**
     * Retrieve current pickup for quote
     *
     * @param string|int $cartId
     * @return string[]|false
     * @throws LocalizedException
     */
    public function currentPickup($cartId)
    {
        $connection = $this->getConnection();

        $pickup = $connection->fetchRow(
            $connection->select()
                ->from(
                    $this->getMainTable(),
                    ['pickup_id', 'country_id', 'code', 'company', 'street', 'postcode', 'city']
                )
                ->where('quote_id = ?', $cartId)
                ->limit(1)
        );

        return $pickup;
    }

    /**
     * Reset pickup data for quote
     *
     * @param string $cartId
     * @return bool
     * @throws LocalizedException
     */
    public function resetPickup($cartId)
    {
        $connection = $this->getConnection();

        $connection->delete(
            $this->getMainTable(),
            [
                'quote_id = ?' => $cartId
            ]
        );

        return true;
    }

    /**
     * Retrieve shipping data for order
     *
     * @param int $orderId
     * @return array
     */
    public function shippingData($orderId)
    {
        $connection = $this->getConnection();

        $data = $connection->fetchRow(
            $connection->select()
                ->from(
                    $this->getTable('sales_order_address'),
                    [
                        ShippingDataInterface::MONDIAL_RELAY_CODE,
                        ShippingDataInterface::MONDIAL_RELAY_PICKUP_ID
                    ]
                )
                ->where(OrderAddressInterface::PARENT_ID . ' = ?', $orderId)
                ->where(OrderAddressInterface::ADDRESS_TYPE . ' = ?', Address::TYPE_SHIPPING)
                ->limit(1)
        );

        return $data;
    }
}
