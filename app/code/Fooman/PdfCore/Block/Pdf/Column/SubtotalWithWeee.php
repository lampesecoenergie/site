<?php
namespace Fooman\PdfCore\Block\Pdf\Column;

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
class SubtotalWithWeee extends Subtotal
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

    public function getSubtotal($row)
    {
        $property = OrderItemInterface::ROW_TOTAL_INCL_TAX;
        $baseProperty = OrderItemInterface::BASE_ROW_TOTAL_INCL_TAX;

        $subtotal = $row->{$this->convertInterfaceConstantToGetter($property)}();
        $baseSubtotal = $row->{$this->convertInterfaceConstantToGetter($baseProperty)}();
        $weeeTaxes = $this->weeeData->getApplied($row);
        $weeeTaxIncl = $this->weeeData->getSalesPriceDisplayType($row->getStoreId());
        $weeeTaxIncl = $weeeTaxIncl == Tax::DISPLAY_INCL || $weeeTaxIncl == Tax::DISPLAY_INCL_DESCR;

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

        $return = $baseSubtotal;
        if ($this->isDisplayingBothCurrencies()) {
            $return = [
                ['currency' => $this->getBaseCurrencyCode(), 'amount' => $baseSubtotal],
                ['currency' => $this->getCurrencyCode(), 'amount' => $subtotal]
            ];

            if (!empty($weeeTaxes)) {
                if ($weeeTaxIncl) {
                    foreach ($weeeTaxes as $weeeTax) {
                        $return[] = [
                            'currency' => $this->getBaseCurrencyCode(),
                            'amount' => $weeeTax['base_row_amount_incl_tax'],
                            'text' => $weeeTax['title'] . ': %s'
                        ];
                        $return[] = [
                            'currency' => $this->getCurrencyCode(),
                            'amount' => $weeeTax['row_amount_incl_tax'],
                            'text' => $weeeTax['title'] . ': %s'
                        ];
                    }
                } else {
                    foreach ($weeeTaxes as $weeeTax) {
                        $return[] = [
                            'currency' => $this->getBaseCurrencyCode(),
                            'amount' => $weeeTax['base_row_amount'],
                            'text' => $weeeTax['title'] . ': %s'
                        ];
                        $return[] = [
                            'currency' => $this->getCurrencyCode(),
                            'amount' => $weeeTax['row_amount'],
                            'text' => $weeeTax['title'] . ': %s'
                        ];
                    }
                }
            }
        } elseif ($this->getUseOrderCurrency()) {
            $return = [
                ['currency' => $this->getCurrencyCode(), 'amount' => $subtotal],
            ];

            if (!empty($weeeTaxes)) {
                foreach ($weeeTaxes as $weeeTax) {
                    if ($weeeTaxIncl) {
                        $return[] = [
                            'currency' => $this->getCurrencyCode(),
                            'amount' => $weeeTax['row_amount_incl_tax'],
                            'text' => $weeeTax['title'] . ': %s'
                        ];
                    } else {
                        $return[] = [
                            'currency' => $this->getCurrencyCode(),
                            'amount' => $weeeTax['row_amount'],
                            'text' => $weeeTax['title'] . ': %s'
                        ];
                    }
                }
            }
        }
        return $return;
    }
}
