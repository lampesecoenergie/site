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
class OriginalPrice extends \Fooman\PdfCore\Block\Pdf\Column implements \Fooman\PdfCore\Block\Pdf\ColumnInterface
{
    const DEFAULT_WIDTH = 12;
    const DEFAULT_TITLE = 'Original Price';
    const COLUMN_TYPE = 'fooman_currency';

    public function getGetter()
    {
        return [$this, 'getPrice'];
    }

    public function getPrice($row)
    {
        $methodIncl = $this->convertInterfaceConstantToGetter(OrderItemInterface::ORIGINAL_PRICE);
        $baseMethodIncl = $this->convertInterfaceConstantToGetter(OrderItemInterface::BASE_ORIGINAL_PRICE);

        if ($row instanceof \Magento\Sales\Api\Data\OrderItemInterface) {
            $item = $row;
        } else {
            $item = $row->getOrderItem();
        }

        $priceIncl = $item->$methodIncl();
        $basePriceIncl = $item->$baseMethodIncl();

        if ($this->isDisplayingBothCurrencies()) {
            return [
                ['currency' => $this->getBaseCurrencyCode(), 'amount' => $basePriceIncl],
                ['currency' => $this->getCurrencyCode(), 'amount' => $priceIncl]
            ];
        } elseif ($this->getUseOrderCurrency()) {
            return [
                ['currency' => $this->getCurrencyCode(), 'amount' => $priceIncl]
            ];
        }
        return [
            ['currency' => $this->getBaseCurrencyCode(), 'amount' => $basePriceIncl]
        ];
    }
}
