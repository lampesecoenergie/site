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
class ProductWeee extends Price
{
    const DEFAULT_WIDTH = 18;
    const DEFAULT_TITLE = 'Fixed Product Taxes';
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
        $weeeTaxes = $this->weeeData->getApplied($row);
        $return = [];

        if (!empty($weeeTaxes)) {
            if ($priceTaxDisplay == TaxConfig::DISPLAY_TYPE_BOTH) {
                if ($this->isDisplayingBothCurrencies()) {
                    foreach ($weeeTaxes as $weeeTax) {
                        if ($weeeTaxIncl) {
                            $return[] = ['currency' => $this->getBaseCurrencyCode(
                            ), 'amount' => $weeeTax['base_amount_incl_tax'], 'text' => $weeeTax['title'] . ': %s'];
                            $return[] = ['currency' => $this->getCurrencyCode(
                            ), 'amount' => $weeeTax['amount_incl_tax'], 'text' => $weeeTax['title'] . ': %s'];
                        } else {
                            $return[] = ['currency' => $this->getBaseCurrencyCode(
                            ), 'amount' => $weeeTax['base_amount'], 'text' => $weeeTax['title'] . ': %s'];
                            $return[] = ['currency' => $this->getCurrencyCode(
                            ), 'amount' => $weeeTax['amount'], 'text' => $weeeTax['title'] . ': %s'];
                        }
                    }
                } elseif ($this->getUseOrderCurrency()) {
                    foreach ($weeeTaxes as $weeeTax) {
                        if ($weeeTaxIncl) {
                            $return[] = ['currency' => $this->getCurrencyCode(
                            ), 'amount' => $weeeTax['amount_incl_tax'], 'text' => $weeeTax['title'] . ': %s'];
                        } else {
                            $return[] = ['currency' => $this->getCurrencyCode(
                            ), 'amount' => $weeeTax['amount'], 'text' => $weeeTax['title'] . ': %s'];
                        }
                    }
                } else {
                    foreach ($weeeTaxes as $weeeTax) {
                        if ($weeeTaxIncl) {
                            $return[] = ['currency' => $this->getBaseCurrencyCode(
                            ), 'amount' => $weeeTax['base_amount_incl_tax'], 'text' => $weeeTax['title'] . ': %s'];
                        } else {
                            $return[] = ['currency' => $this->getBaseCurrencyCode(
                            ), 'amount' => $weeeTax['base_amount'], 'text' => $weeeTax['title'] . ': %s'];
                        }
                    }
                }
            } elseif ($priceTaxDisplay == TaxConfig::DISPLAY_TYPE_EXCLUDING_TAX) {
                if ($this->isDisplayingBothCurrencies()) {
                    foreach ($weeeTaxes as $weeeTax) {
                        if ($weeeTaxIncl) {
                            $return[] = ['currency' => $this->getBaseCurrencyCode(
                            ), 'amount' => $weeeTax['base_amount_incl_tax'], 'text' => $weeeTax['title'] . ': %s'];
                            $return[] = ['currency' => $this->getCurrencyCode(
                            ), 'amount' => $weeeTax['amount_incl_tax'], 'text' => $weeeTax['title'] . ': %s'];
                        } else {
                            $return[] = ['currency' => $this->getBaseCurrencyCode(
                            ), 'amount' => $weeeTax['base_amount'], 'text' => $weeeTax['title'] . ': %s'];
                            $return[] = ['currency' => $this->getCurrencyCode(
                            ), 'amount' => $weeeTax['amount'], 'text' => $weeeTax['title'] . ': %s'];
                        }
                    }
                } elseif ($this->getUseOrderCurrency()) {
                    foreach ($weeeTaxes as $weeeTax) {
                        if ($weeeTaxIncl) {
                            $return[] = ['currency' => $this->getCurrencyCode(
                            ), 'amount' => $weeeTax['amount_incl_tax'], 'text' => $weeeTax['title'] . ': %s'];
                        } else {
                            $return[] = ['currency' => $this->getCurrencyCode(
                            ), 'amount' => $weeeTax['amount'], 'text' => $weeeTax['title'] . ': %s'];
                        }
                    }
                } else {
                    foreach ($weeeTaxes as $weeeTax) {
                        if ($weeeTaxIncl) {
                            $return[] = ['currency' => $this->getCurrencyCode(
                            ), 'amount' => $weeeTax['base_amount_incl_tax'], 'text' => $weeeTax['title'] . ': %s'];
                        } else {
                            $return[] = ['currency' => $this->getCurrencyCode(
                            ), 'amount' => $weeeTax['base_amount'], 'text' => $weeeTax['title'] . ': %s'];
                        }
                    }
                }
            } else {
                if ($this->isDisplayingBothCurrencies()) {
                    foreach ($weeeTaxes as $weeeTax) {
                        if ($weeeTaxIncl) {
                            $return[] = ['currency' => $this->getBaseCurrencyCode(
                            ), 'amount' => $weeeTax['base_amount_incl_tax'], 'text' => $weeeTax['title'] . ': %s'];
                            $return[] = ['currency' => $this->getCurrencyCode(
                            ), 'amount' => $weeeTax['amount_incl_tax'], 'text' => $weeeTax['title'] . ': %s'];
                        } else {
                            $return[] = ['currency' => $this->getBaseCurrencyCode(
                            ), 'amount' => $weeeTax['base_amount'], 'text' => $weeeTax['title'] . ': %s'];
                            $return[] = ['currency' => $this->getCurrencyCode(
                            ), 'amount' => $weeeTax['amount'], 'text' => $weeeTax['title'] . ': %s'];
                        }
                    }
                } elseif ($this->getUseOrderCurrency()) {
                    foreach ($weeeTaxes as $weeeTax) {
                        if ($weeeTaxIncl) {
                            $return[] = ['currency' => $this->getCurrencyCode(
                            ), 'amount' => $weeeTax['amount_incl_tax'], 'text' => $weeeTax['title'] . ': %s'];
                        } else {
                            $return[] = ['currency' => $this->getCurrencyCode(
                            ), 'amount' => $weeeTax['amount'], 'text' => $weeeTax['title'] . ': %s'];
                        }
                    }
                } else {
                    foreach ($weeeTaxes as $weeeTax) {
                        if ($weeeTaxIncl) {
                            $return[] = ['currency' => $this->getBaseCurrencyCode(
                            ), 'amount' => $weeeTax['base_amount_incl_tax'], 'text' => $weeeTax['title'] . ': %s'];
                        } else {
                            $return[] = ['currency' => $this->getBaseCurrencyCode(
                            ), 'amount' => $weeeTax['base_amount'], 'text' => $weeeTax['title'] . ': %s'];
                        }
                    }
                }
            }
        }

        return $return;
    }
}
