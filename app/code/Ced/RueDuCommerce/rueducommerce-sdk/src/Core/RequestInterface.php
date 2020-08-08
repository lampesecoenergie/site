<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @package     RueDuCommerce-Sdk
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace RueDuCommerceSdk\Core;

/**
 * Directory separator shorthand
 */
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * @api
 */
interface RequestInterface
{
    /**
     * Default API URLs
     */
    const CATCH_API_URL = 'https://mirakl-web.groupe-rueducommerce.fr/';
    const CATCH_SANDBOX_API_URL = 'https://mirakl-web-preprod.groupe-rueducommerce.fr/';

    const ORDERS_API_URL = 'https://marchands.rueducommerce.fr/';

    const GET_CATEGORIES_SUB_URL = 'api/hierarchies';
    const GET_ATTRIBUTES_SUB_URL = 'api/products/attributes';
    const GET_ATTRIBUTES_VALUE_LIST = 'api/values_lists?code=';
    const GET_OFFERS = 'api/offers';

    const GET_ORDERS_SUB_URL = 'api/orders';

    const GET_ORDERS_URL = 'merchant/syndication/orders/e-Avantage';
    const PUT_ORDERS_SHIPMENT_URL = 'merchant/mmie/xml/e-Avantage';

    const GET_ORDERS_DOCUMENT_URL = 'api/orders/documents';
    const GET_ORDERS_DOCUMENT_DOWNLOAD_URL = '/api/orders/documents/download';
    const PUT_SHIPMENT_SUB_URL = 'oms/asn/v7?sellerId=';
    const GET_FEEDS_SUB_URL = 'api/products/imports/%s/error_report';
    const POST_ITEMS_SUB_URL = 'api/products/imports';
    const POST_OFFER_IMPORT  = 'api/offers/imports';
    const PUT_INVENTORY_SUB_URL = 'inventory/fbm-lmp/v7?sellerId=';
    const GET_INVENTORY_SUB_URL = 'inventory/v5?itemIds=%s&sellerId=%s';
    const PUT_PRICE_SUB_URL = 'pricing/fbm/v5?sellerId=';
    const PUT_ORDER_ACCEPTANCE = 'api/orders/';
    const PUT_ORDER_REFUND = '/api/orders/refund';
    const GET_SHIPPING_CARRIERS = 'api/shipping/carriers';
    const GET_REASONS = 'api/reasons';
    const PUT_REFUND_URL = 'api/orders/refund';

    const SSL_VERIFY = false;

    const FEED_CODE_ORDER_CREATE = 'order-create';
    const FEED_CODE_ITEM_UPDATE = 'item-update';
    const FEED_CODE_ITEM_DEACTIVATE = 'item-deactivate';
    const FEED_CODE_ITEM_DELETE = 'item-delete';
    const FEED_CODE_INVENTORY_UPDATE = 'inventory-update';
    const FEED_CODE_PRICE_UPDATE = 'price-update';
    const FEED_CODE_ORDER_SHIPMENT = 'order-shipment';
    const FEED_CANCEL_ORDER_ITEM = 'order-accept';
    /**
     * Get Request
     * @param $url
     * @param array $params
     * @return mixed
     */
    public function getRequest($url, $params = []);

    /**
     * Put Request
     * @param $url
     * @param array $params
     * @return mixed
     */
    public function putRequest($url, $params = []);

    /**
     * Post Request
     * @param $url
     * @param array $params
     * @return mixed
     */
    public function postRequest($url, $params = []);

}
