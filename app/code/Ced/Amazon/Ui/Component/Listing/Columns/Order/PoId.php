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
 * @category    Ced
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Ui\Component\Listing\Columns\Order;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class PoId
 * @package Ced\Amazon\Ui\Component\Listing\Columns\Order
 */
class PoId extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = 'amazon_order_id';
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$fieldName], $item['marketplace_id'])) {
                    $url = \Amazon\Sdk\Marketplace::getSellerCentralByMarketplaceId($item['marketplace_id']);
                    $html = "<a href='" . $url . 'orders-v3/order/' . $item[$fieldName] . "' target='_blank'>";
                    $html .= $item[$fieldName];
                    $html .= "</a>";
                    $item[$fieldName . '_html'] = $html;
                }
            }
        }
        return $dataSource;
    }
}
