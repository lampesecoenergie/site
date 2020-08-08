<?php
namespace Fooman\PdfCore\Block\Pdf\Column;

use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Subtotal extends \Fooman\PdfCore\Block\Pdf\Column implements \Fooman\PdfCore\Block\Pdf\ColumnInterface
{
    const DEFAULT_WIDTH = 12;
    const DEFAULT_TITLE = 'Subtotal';
    const COLUMN_TYPE = 'fooman_currency';

    public function getGetter()
    {
        return [$this, 'getSubtotal'];
    }

    public function getSubtotal($row)
    {
        $property = OrderItemInterface::ROW_TOTAL_INCL_TAX;
        $baseProperty = OrderItemInterface::BASE_ROW_TOTAL_INCL_TAX;

        $subtotal = $row->{$this->convertInterfaceConstantToGetter($property)}();
        $baseSubtotal = $row->{$this->convertInterfaceConstantToGetter($baseProperty)}();

        if (null === $subtotal) {
            $property = OrderItemInterface::ROW_TOTAL;
            $baseProperty = OrderItemInterface::BASE_ROW_TOTAL;
            $subtotal = $row->{$this->convertInterfaceConstantToGetter($property)}();
            $baseSubtotal = $row->{$this->convertInterfaceConstantToGetter($baseProperty)}();

            $taxProperty = OrderItemInterface::TAX_AMOUNT;
            $baseTaxProperty = OrderItemInterface::BASE_TAX_AMOUNT;

            $subtotal += $row->{$this->convertInterfaceConstantToGetter($taxProperty)}();
            $baseSubtotal += $row->{$this->convertInterfaceConstantToGetter($baseTaxProperty)}();
        }

        if ($this->isDisplayingBothCurrencies()) {
            return [
                ['currency' => $this->getBaseCurrencyCode(), 'amount' => $baseSubtotal],
                ['currency' => $this->getCurrencyCode(), 'amount' => $subtotal]
            ];
        } elseif ($this->getUseOrderCurrency()) {
            return $subtotal;
        }
        return $baseSubtotal;
    }
}
