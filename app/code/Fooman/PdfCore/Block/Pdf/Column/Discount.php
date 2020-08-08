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
class Discount extends \Fooman\PdfCore\Block\Pdf\Column implements \Fooman\PdfCore\Block\Pdf\ColumnInterface
{
    const DEFAULT_WIDTH = 12;
    const DEFAULT_TITLE = 'Discount';
    const COLUMN_TYPE = 'fooman_currency';

    public function getGetter()
    {
        return [$this, 'getDiscount'];
    }

    public function getDiscount($row)
    {
        $property = \Magento\Sales\Api\Data\OrderItemInterface::DISCOUNT_AMOUNT;
        $baseProperty = \Magento\Sales\Api\Data\OrderItemInterface::BASE_DISCOUNT_AMOUNT;

        $subtotal = $row->{$this->convertInterfaceConstantToGetter($property)}();
        $baseSubtotal = $row->{$this->convertInterfaceConstantToGetter($baseProperty)}();

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
