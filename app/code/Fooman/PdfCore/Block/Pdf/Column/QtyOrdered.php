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
class QtyOrdered extends \Fooman\PdfCore\Block\Pdf\Column implements \Fooman\PdfCore\Block\Pdf\ColumnInterface
{
    const DEFAULT_WIDTH = 12;
    const DEFAULT_TITLE = 'Qty';
    const COLUMN_TYPE = 'fooman_qtyOrdered';

    public function getGetter()
    {
        return $this->convertInterfaceConstantToGetter(\Magento\Sales\Api\Data\OrderItemInterface::QTY_ORDERED);
    }
}
