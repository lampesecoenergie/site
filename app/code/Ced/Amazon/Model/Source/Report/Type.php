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
 * @category  Ced
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Model\Source\Report;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Type
 * @package Ced\Amazon\Model\Source
 */
class Type extends AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_OPEN_LISTING_ALL_DATA,
                'label' => __('All Listing Data (TSV)'),
            ],
            [
                'value' => \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_OPEN_LISTING_LITE,
                'label' => __('All Listing Lite (TSV)'),
            ],
            [
                'value' => \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_FBA_XML_ORDER_DATA,
                'label' => __('All FBA Orders (XML)'),
            ],
            // Use it to get all FBA orders with customer names
            [
                'value' => \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_FBA_SHIPMENTS_DATA,
                'label' => __('All FBA Orders Shipments (TSV)'),
            ],
            [
                'value' => \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_FBA_FLAT_ORDER_DATA,
                'label' => __('All Order Tracking Reports (TSV)'),
            ],
            [
                'value' => \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_ORDER_DATA,
                'label' => __('All Marketplace Orders (TSV)'),
            ],
            [
                'value' => \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_UNSHIPPED_ORDER_DATA,
                'label' => __('All Marketplace Unshipped Orders (TSV)'),
            ],
            [
                'value' => \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_DEFECT_LISTING_DATA,
                'label' => __('All Suppressed Listing (TSV)'),
            ],
            [
                'value' => \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_INVENTORY,
                'label' => __('All Open Listing Inventory (TSV)'),
            ],
            [
                'value' => \Amazon\Sdk\Api\Report\Request::REPORT_TYPE_GLOBAL_OPPORTUNITIES,
                'label' => __('All Global Expansion Opportunities (TSV - US Only)'),
            ],
        ];
    }
}
