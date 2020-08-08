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
class Rowtotal extends \Fooman\PdfCore\Block\Pdf\Column implements \Fooman\PdfCore\Block\Pdf\ColumnInterface
{
    const DEFAULT_WIDTH = 12;
    const DEFAULT_TITLE = 'Row Total';
    const COLUMN_TYPE = 'fooman_currency';

    public function getGetter()
    {
        return [$this, 'getRowTotal'];
    }

    /**
     * @see \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer::getTotalAmount()
     * @param $row
     *
     * @return array
     */
    public function getRowTotal($row)
    {
        $rowTotal = $this->getValueViaConstant($row, OrderItemInterface::ROW_TOTAL);
        $tax = $this->getValueViaConstant($row, OrderItemInterface::TAX_AMOUNT);
        $taxComp = $this->getValueViaConstant($row, OrderItemInterface::DISCOUNT_TAX_COMPENSATION_AMOUNT);
        $discount = $this->getValueViaConstant($row, OrderItemInterface::DISCOUNT_AMOUNT);
        $weee = $this->getValueViaConstant($row, OrderItemInterface::WEEE_TAX_APPLIED_ROW_AMOUNT);

        $baseRowTotal = $this->getValueViaConstant($row, OrderItemInterface::BASE_ROW_TOTAL);
        $baseTax = $this->getValueViaConstant($row, OrderItemInterface::BASE_TAX_AMOUNT);
        $baseTaxComp = $this->getValueViaConstant($row, OrderItemInterface::BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT);
        $baseDiscount = $this->getValueViaConstant($row, OrderItemInterface::BASE_DISCOUNT_AMOUNT);
        $baseWeee = $this->getValueViaConstant($row, OrderItemInterface::BASE_WEEE_TAX_APPLIED_ROW_AMNT);

        $displayRowTotal = $rowTotal + $tax + $taxComp + $weee - $discount;
        $displayBaseRowTotal = $baseRowTotal + $baseTax + $baseTaxComp + $baseWeee - $baseDiscount;

        if ($this->isDisplayingBothCurrencies()) {
            return [
                ['currency' => $this->getBaseCurrencyCode(), 'amount' => $displayBaseRowTotal],
                ['currency' => $this->getCurrencyCode(), 'amount' => $displayRowTotal]
            ];
        } elseif ($this->getUseOrderCurrency()) {
            return $displayRowTotal;
        }
        return $displayBaseRowTotal;
    }

    private function getValueViaConstant($row, $constant)
    {
        return $row->{$this->convertInterfaceConstantToGetter($constant)}();
    }
}
