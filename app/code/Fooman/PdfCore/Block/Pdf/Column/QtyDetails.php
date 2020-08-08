<?php
namespace Fooman\PdfCore\Block\Pdf\Column;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class QtyDetails extends \Fooman\PdfCore\Block\Pdf\Column implements \Fooman\PdfCore\Block\Pdf\ColumnInterface
{
    const DEFAULT_WIDTH = 16;
    const DEFAULT_TITLE = 'Qty';
    const COLUMN_TYPE = 'fooman_qtyDetails';
    const XML_PATH_QTY_AS_INT = 'sales_pdf/all/allqtyasint';

    public function getGetter()
    {
        return [$this, 'getQtyDetails'];
    }

    public function getQtyDetails($row)
    {
        $showAsInt = $this->_scopeConfig->getValue(
            self::XML_PATH_QTY_AS_INT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $row->getStoreId()
        );

        if ($row instanceof \Magento\Sales\Api\Data\OrderItemInterface) {
            $orderItem = $row;
        } else {
            $orderItem = $row->getOrderItem();
        }

        $data = [];
        $value = $showAsInt ? (int)$orderItem->getQtyOrdered() : $orderItem->getQtyOrdered();
        $data[] = __('Ordered') . ' ' . $value;
        if ($orderItem->getQtyInvoiced() > 0.0001) {
            $value = $showAsInt ? (int)$orderItem->getQtyInvoiced() : $orderItem->getQtyInvoiced();
            $data[] = __('Invoiced') . ' ' . $value;
        }
        if ($orderItem->getQtyShipped() > 0.0001) {
            $value = $showAsInt ? (int)$orderItem->getQtyShipped() : $orderItem->getQtyShipped();
            $data[] = __('Shipped') . ' ' . $value;
        }
        if ($orderItem->getQtyRefunded() > 0.0001) {
            $value = $showAsInt ? (int)$orderItem->getQtyRefunded() : $orderItem->getQtyRefunded();
            $data[] = __('Refunded') . ' ' . $value;
        }
        if ($orderItem->getQtyCanceled() > 0.0001) {
            $value = $showAsInt ? (int)$orderItem->getQtyCanceled() : $orderItem->getQtyCanceled();
            $data[] = __('Canceled') . ' ' . $value;
        }
        if ($orderItem->getQtyBackordered() > 0.0001) {
            $value = $showAsInt ? (int)$orderItem->getQtyBackordered() : $orderItem->getQtyBackordered();
            $data[] = __('Backordered') . ' ' . $value;
        }

        return implode('<br/>', $data);
    }
}
