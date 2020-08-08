<?php
namespace Fooman\PdfCore\Block\Pdf\Column;

use Magento\Tax\Model\Config as TaxConfig;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Price extends \Fooman\PdfCore\Block\Pdf\Column implements \Fooman\PdfCore\Block\Pdf\ColumnInterface
{
    const DEFAULT_WIDTH = 12;
    const DEFAULT_TITLE = 'Price';
    const COLUMN_TYPE = 'fooman_currency';

    public function getGetter()
    {
        return [$this, 'getPrice'];
    }

    public function getPrice($row)
    {
        if ($row instanceof \Magento\Sales\Api\Data\OrderItemInterface) {
            $storedId = $row->getStoreId();
        } else {
            $storedId = $row->getOrderItem()->getStoreId();
        }

        $priceTaxDisplay = $this->_scopeConfig->getValue(
            TaxConfig::XML_PATH_DISPLAY_SALES_PRICE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storedId
        );

        $methodExcl = $this->convertInterfaceConstantToGetter(OrderItemInterface::PRICE);
        $baseMethodExcl = $this->convertInterfaceConstantToGetter(OrderItemInterface::BASE_PRICE);
        $methodIncl = $this->convertInterfaceConstantToGetter(OrderItemInterface::PRICE_INCL_TAX);
        $baseMethodIncl = $this->convertInterfaceConstantToGetter(OrderItemInterface::BASE_PRICE_INCL_TAX);

        if ($row instanceof \Magento\Sales\Api\Data\OrderItemInterface) {
            $item = $row;
        } else {
            $item = $row->getOrderItem();
        }

        $priceExcl = $item->$methodExcl();
        $basePriceExcl = $item->$baseMethodExcl();
        $priceIncl = $item->$methodIncl();
        $basePriceIncl = $item->$baseMethodIncl();

        if ($priceTaxDisplay == TaxConfig::DISPLAY_TYPE_BOTH) {
            if ($this->isDisplayingBothCurrencies()) {
                return [
                    ['currency' => $this->getBaseCurrencyCode(), 'amount' => $basePriceExcl],
                    ['currency' => $this->getBaseCurrencyCode(), 'amount' => $basePriceIncl],
                    ['currency' => $this->getCurrencyCode(), 'amount' => $priceExcl],
                    ['currency' => $this->getCurrencyCode(), 'amount' => $priceIncl]
                ];
            } elseif ($this->getUseOrderCurrency()) {
                return [
                    ['currency' => $this->getCurrencyCode(), 'amount' => $priceExcl],
                    ['currency' => $this->getCurrencyCode(), 'amount' => $priceIncl]
                ];
            }
            return [
                ['currency' => $this->getBaseCurrencyCode(), 'amount' => $basePriceExcl],
                ['currency' => $this->getBaseCurrencyCode(), 'amount' => $basePriceIncl]
            ];
        } elseif ($priceTaxDisplay == TaxConfig::DISPLAY_TYPE_EXCLUDING_TAX) {
            if ($this->isDisplayingBothCurrencies()) {
                return [
                    ['currency' => $this->getBaseCurrencyCode(), 'amount' => $basePriceExcl],
                    ['currency' => $this->getCurrencyCode(), 'amount' => $priceExcl]
                ];
            } elseif ($this->getUseOrderCurrency()) {
                return [
                    ['currency' => $this->getCurrencyCode(), 'amount' => $priceExcl]
                ];
            }
            return [
                ['currency' => $this->getBaseCurrencyCode(), 'amount' => $basePriceExcl]
            ];
        }

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
