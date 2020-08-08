<?php
namespace Fooman\PdfCore\Block\Pdf\Column;

use Magento\Tax\Model\Config as TaxConfig;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Weee\Model\Tax;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class PriceWithWeee extends Price
{

    const COLUMN_TYPE = 'fooman_currencyWithText';

    /**
     * @var \Magento\Weee\Helper\Data
     */
    private $weeeData;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Weee\Helper\Data $weeeData,
        array $data = []
    ) {
        $this->weeeData = $weeeData;
        parent::__construct($context, $data);
    }

    public function getPrice($row)
    {
        if ($row instanceof OrderItemInterface) {
            $storedId = $row->getStoreId();
        } else {
            $storedId = $row->getOrderItem()->getStoreId();
        }

        $priceTaxDisplay = $this->_scopeConfig->getValue(
            TaxConfig::XML_PATH_DISPLAY_SALES_PRICE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storedId
        );
        $weeeTaxIncl = $this->weeeData->getSalesPriceDisplayType($row->getStoreId());
        $weeeTaxIncl = $weeeTaxIncl == Tax::DISPLAY_INCL || $weeeTaxIncl == Tax::DISPLAY_INCL_DESCR;

        $methodExcl = $this->convertInterfaceConstantToGetter(OrderItemInterface::PRICE);
        $baseMethodExcl = $this->convertInterfaceConstantToGetter(OrderItemInterface::BASE_PRICE);
        $methodIncl = $this->convertInterfaceConstantToGetter(OrderItemInterface::PRICE_INCL_TAX);
        $baseMethodIncl = $this->convertInterfaceConstantToGetter(OrderItemInterface::BASE_PRICE_INCL_TAX);

        if ($row instanceof OrderItemInterface) {
            $item = $row;
        } else {
            $item = $row->getOrderItem();
        }

        $priceExcl = $item->$methodExcl();
        $basePriceExcl = $item->$baseMethodExcl();
        $priceIncl = $item->$methodIncl();
        $basePriceIncl = $item->$baseMethodIncl();
        $weeeTaxes = $this->weeeData->getApplied($row);

        $baseCurrency = $this->getBaseCurrencyCode();
        $currency = $this->getCurrencyCode();

        if ($priceTaxDisplay == TaxConfig::DISPLAY_TYPE_BOTH) {
            if ($this->isDisplayingBothCurrencies()) {
                $return = [
                    ['currency' => $baseCurrency, 'amount' => $basePriceExcl],
                    ['currency' => $baseCurrency, 'amount' => $basePriceIncl],
                    ['currency' => $currency, 'amount' => $priceExcl],
                    ['currency' => $currency, 'amount' => $priceIncl]
                ];

                if (!empty($weeeTaxes)) {
                    foreach ($weeeTaxes as $weeeTax) {
                        if ($weeeTaxIncl) {
                            $return[] = $this->prepReturn(
                                $baseCurrency,
                                $weeeTax['base_amount_incl_tax'],
                                $weeeTax['title'] . ': %s'
                            );
                            $return[] = $this->prepReturn(
                                $currency,
                                $weeeTax['amount_incl_tax'],
                                $weeeTax['title'].': %s'
                            );
                        } else {
                            $return[] = $this->prepReturn(
                                $baseCurrency,
                                $weeeTax['base_amount'],
                                $weeeTax['title'].': %s'
                            );
                            $return[] = $this->prepReturn(
                                $currency,
                                $weeeTax['amount'],
                                $weeeTax['title'].': %s'
                            );
                        }
                    }
                }
            } elseif ($this->getUseOrderCurrency()) {
                $return = [
                    ['currency' => $currency, 'amount' => $priceExcl],
                    ['currency' => $currency, 'amount' => $priceIncl]
                ];

                if (!empty($weeeTaxes)) {
                    foreach ($weeeTaxes as $weeeTax) {
                        if ($weeeTaxIncl) {
                            $return[] = $this->prepReturn(
                                $currency,
                                $weeeTax['amount_incl_tax'],
                                $weeeTax['title'] . ': %s'
                            );
                        } else {
                            $return[] = $this->prepReturn(
                                $currency,
                                $weeeTax['amount'],
                                $weeeTax['title'] . ': %s'
                            );
                        }
                    }
                }
            } else {
                $return =  [
                    ['currency' => $baseCurrency, 'amount' => $basePriceExcl],
                    ['currency' => $baseCurrency, 'amount' => $basePriceIncl]
                ];

                if (!empty($weeeTaxes)) {
                    foreach ($weeeTaxes as $weeeTax) {
                        if ($weeeTaxIncl) {
                            $return[] = $this->prepReturn(
                                $baseCurrency,
                                $weeeTax['base_amount_incl_tax'],
                                $weeeTax['title'].': %s'
                            );
                        } else {
                            $return[] = $this->prepReturn(
                                $baseCurrency,
                                $weeeTax['base_amount'],
                                $weeeTax['title'].': %s'
                            );
                        }
                    }
                }
            }
        } elseif ($priceTaxDisplay == TaxConfig::DISPLAY_TYPE_EXCLUDING_TAX) {
            if ($this->isDisplayingBothCurrencies()) {
                $return = [
                    ['currency' => $baseCurrency, 'amount' => $basePriceExcl],
                    ['currency' => $currency, 'amount' => $priceExcl]
                ];

                if (!empty($weeeTaxes)) {
                    foreach ($weeeTaxes as $weeeTax) {
                        if ($weeeTaxIncl) {
                            $return[] = $this->prepReturn(
                                $baseCurrency,
                                $weeeTax['base_amount_incl_tax'],
                                $weeeTax['title'].': %s'
                            );
                            $return[] = $this->prepReturn(
                                $currency,
                                $weeeTax['amount_incl_tax'],
                                $weeeTax['title'].': %s'
                            );
                        } else {
                            $return[] = $this->prepReturn(
                                $baseCurrency,
                                $weeeTax['base_amount'],
                                $weeeTax['title'].': %s'
                            );
                            $return[] = $this->prepReturn($currency, $weeeTax['amount'], $weeeTax['title'].': %s');
                        }
                    }
                }
            } elseif ($this->getUseOrderCurrency()) {
                $return = [
                    ['currency' => $currency, 'amount' => $priceExcl]
                ];

                if (!empty($weeeTaxes)) {
                    foreach ($weeeTaxes as $weeeTax) {
                        if ($weeeTaxIncl) {
                            $return[] = $this->prepReturn(
                                $currency,
                                $weeeTax['amount_incl_tax'],
                                $weeeTax['title'].': %s'
                            );
                        } else {
                            $return[] = $this->prepReturn($currency, $weeeTax['amount'], $weeeTax['title'].': %s');
                        }
                    }
                }
            } else {
                $return = [
                    ['currency' => $baseCurrency, 'amount' => $basePriceExcl]
                ];
                if (!empty($weeeTaxes)) {
                    foreach ($weeeTaxes as $weeeTax) {
                        if ($weeeTaxIncl) {
                            $return[] = $this->prepReturn(
                                $currency,
                                $weeeTax['base_amount_incl_tax'],
                                $weeeTax['title'].': %s'
                            );
                        } else {
                            $return[] = $this->prepReturn(
                                $currency,
                                $weeeTax['base_amount'],
                                $weeeTax['title'].': %s'
                            );
                        }
                    }
                }
            }
        } else {
            if ($this->isDisplayingBothCurrencies()) {
                $return = [
                    ['currency' => $baseCurrency, 'amount' => $basePriceIncl],
                    ['currency' => $currency, 'amount' => $priceIncl]
                ];
                if (!empty($weeeTaxes)) {
                    foreach ($weeeTaxes as $weeeTax) {
                        if ($weeeTaxIncl) {
                            $return[] = $this->prepReturn(
                                $baseCurrency,
                                $weeeTax['base_amount_incl_tax'],
                                $weeeTax['title'].': %s'
                            );
                            $return[] = $this->prepReturn(
                                $currency,
                                $weeeTax['amount_incl_tax'],
                                $weeeTax['title'].': %s'
                            );
                        } else {
                            $return[] = $this->prepReturn(
                                $baseCurrency,
                                $weeeTax['base_amount'],
                                $weeeTax['title'].': %s'
                            );
                            $return[] = $this->prepReturn($currency, $weeeTax['amount'], $weeeTax['title'].': %s');
                        }
                    }
                }
            } elseif ($this->getUseOrderCurrency()) {
                $return = [
                    ['currency' => $currency, 'amount' => $priceIncl]
                ];
                if (!empty($weeeTaxes)) {
                    foreach ($weeeTaxes as $weeeTax) {
                        if ($weeeTaxIncl) {
                            $return[] = $this->prepReturn(
                                $currency,
                                $weeeTax['amount_incl_tax'],
                                $weeeTax['title'].': %s'
                            );
                        } else {
                            $return[] = $this->prepReturn($currency, $weeeTax['amount'], $weeeTax['title'].': %s');
                        }
                    }
                }
            } else {
                $return = [
                    ['currency' => $baseCurrency, 'amount' => $basePriceIncl]
                ];
                if (!empty($weeeTaxes)) {
                    foreach ($weeeTaxes as $weeeTax) {
                        if ($weeeTaxIncl) {
                            $return[] = $this->prepReturn(
                                $baseCurrency,
                                $weeeTax['base_amount_incl_tax'],
                                $weeeTax['title'].': %s'
                            );
                        } else {
                            $return[] = $this->prepReturn(
                                $baseCurrency,
                                $weeeTax['base_amount'],
                                $weeeTax['title'].': %s'
                            );
                        }
                    }
                }
            }
        }

        return $return;
    }

    private function prepReturn($currency, $amount, $title)
    {
        return [
            'currency' => $currency,
            'amount' => $amount,
            'text' => $title
        ];
    }
}
