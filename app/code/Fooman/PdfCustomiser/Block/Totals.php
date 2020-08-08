<?php
namespace Fooman\PdfCustomiser\Block;

/**
 * Block in pdf to display order totals
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Totals extends \Fooman\PdfCore\Block\Pdf\Block
{
    // phpcs:ignore PSR2.Classes.PropertyDeclaration
    protected $_template = 'Fooman_PdfCustomiser::pdf/totals.phtml';

    private $totalsHelper;
    private $taxConfig;
    private $priceCurrency;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Fooman\PdfCustomiser\Helper\Totals              $totalsHelper
     * @param \Magento\Tax\Model\Config                        $taxConfig
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Fooman\PdfCustomiser\Helper\Totals $totalsHelper,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->totalsHelper = $totalsHelper;
        $this->taxConfig = $taxConfig;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal[]
     */
    public function getTotals()
    {
        $totals = $this->totalsHelper->getTotalsList();
        $filterTotals = [];
        if ($this->getSalesObject() instanceof \Magento\Sales\Api\Data\CreditmemoInterface) {
            return $totals;
        }
        foreach ($totals as $total) {
            if ($total->getSourceField() !== 'adjustment_positive'
                && $total->getSourceField() !== 'adjustment_negative') {
                $filterTotals[] = $total;
            }
        }
        return $filterTotals;
    }

    public function getAllTotalLinesForDisplay()
    {
        $allTotalsForDisplay = [[]];
        foreach ($this->getTotals() as $total) {
            $this->prepareTotal($total);
            if ($total->canDisplay()) {
                $allTotalsForDisplay[] = $this->getTotalLinesForDisplay($total);
            }
        }

        return array_merge(...$allTotalsForDisplay);
    }

    /**
     * @param \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal $total
     *
     * @return \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
     */
    public function prepareTotal(\Magento\Sales\Model\Order\Pdf\Total\DefaultTotal $total)
    {
        $total->setOrder($this->getOrder());
        $total->setSource($this->getSalesObject());
        $this->getSalesObject()->setOrder($this->getOrder());
        return $total;
    }

    public function getTotalLinesForDisplay(\Magento\Sales\Model\Order\Pdf\Total\DefaultTotal $total)
    {
        $totalLines = $total->getTotalsForDisplay();
        if ($this->isTaxDisplayedWithGrandTotal($total)) {
            $fullTaxInfo = $this->getFixedTaxTotals($total);
            $grandTotalExl = array_shift($totalLines);
            $baseAmount = $this->getSalesObject()->getBaseGrandTotal();
            $baseTaxAmount = $this->getSalesObject()->getBaseTaxAmount();

            $grandTotalExl['base_amount'] = $this->getAmountPrefix()
                . $this->getOrder()->getBaseCurrency()->formatTxt($baseAmount - $baseTaxAmount);

            $grandTotalIncl = array_pop($totalLines);
            $grandTotalIncl['base_amount'] = $this->getAmountPrefix()
                . $this->getOrder()->getBaseCurrency()->formatTxt($baseAmount);

            if ($baseTaxAmount > 0) {
                array_unshift($fullTaxInfo, $grandTotalExl);
                $totalLines = $fullTaxInfo;
            } else {
                $totalLines = [$grandTotalExl];
                // should be 0 - due to an issue with Magento it will wrongly retrieve the tax rates from the order
                // in case we are displaying a credit memo with no items
                // we loop over the rates to get the label
                foreach ($fullTaxInfo as $taxLine) {
                    $line = [];
                    $line['label'] = $taxLine['label'];
                    $line['amount'] = $this->getAmountPrefix()
                        . $this->getOrder()->getOrderCurrency()->formatTxt($this->getSalesObject()->getTaxAmount());
                    $line['base_amount'] = $this->getAmountPrefix()
                        . $this->getOrder()->getBaseCurrency()->formatTxt($this->getSalesObject()->getBaseTaxAmount());
                    $totalLines[] = $line;
                }
            }

            $totalLines[] = $grandTotalIncl;
        } elseif ($this->isFullTaxDisplayed($total)) {
            $fullTaxInfo = $this->getFixedTaxTotals($total);
            $totalLines = [];
            if ($this->getSalesObject()->getBaseTaxAmount() > 0) {
                $totalLines = $fullTaxInfo;
            } else {
                // should be 0 - due to an issue with Magento it will wrongly retrieve the tax rates from the order
                // in case we are displaying a credit memo with no items
                // we loop over the rates to get the label
                foreach ($fullTaxInfo as $taxLine) {
                    $line = [];
                    $line['label'] = $taxLine['title'] . ' (' . (float)$taxLine['percent'] . '%):';
                    $line['amount'] = $this->getAmountPrefix()
                        . $this->getOrder()->getOrderCurrency()->formatTxt($this->getSalesObject()->getTaxAmount());
                    $line['base_amount'] = $this->getAmountPrefix()
                        . $this->getOrder()->getBaseCurrency()->formatTxt($this->getSalesObject()->getBaseTaxAmount());
                    $totalLines[] = $line;
                }
            }
        } else {
            foreach ($totalLines as $key => $line) {
                if (!isset($line['base_amount'])) {
                    $amountOnly = $this->getSalesObject()->getDataUsingMethod($total->getSourceField());
                    $baseAmount = $this->getSalesObject()->getDataUsingMethod('base_' . $total->getSourceField());
                    //the tax inclusive amounts are added after the exclusive amounts in core Magento
                    if ($this->shouldAdjustBaseAmount($key, $total->getSourceField())) {
                        $sourceField = $total->getSourceField();
                        if ($total->getSourceField() == 'shipping_amount') {
                            $sourceField = 'shipping';
                        }
                        $baseMethodName = 'base_' . $sourceField . '_incl_tax';
                        $baseAmount = $this->getSalesObject()->getDataUsingMethod($baseMethodName);
                        if (!$baseAmount) {
                            $baseAmount = $this->priceCurrency->round(
                                $amountOnly / $this->getOrder()->getBaseToOrderRate()
                            );
                        }
                    }

                    $line['base_amount'] = $this->getAmountPrefix()
                        . $this->getOrder()->getBaseCurrency()->formatTxt($baseAmount);
                }

                $totalLines[$key] = $line;
            }
        }

        foreach ($totalLines as &$totalLine) {
            $totalLine['source_field'] = $total->getSourceField();
        }
        return $totalLines;
    }

    private function shouldAdjustBaseAmount($key, $sourceField)
    {
        if (!$this->shouldDisplayBothCurrencies()) {
            return false;
        }
        switch ($sourceField) {
            case 'shipping_amount':
                return $this->taxConfig->displaySalesShippingInclTax()
                    || ($this->taxConfig->displaySalesShippingBoth() && $key == 1);
            case 'subtotal':
            default:
                return $this->taxConfig->displaySalesSubtotalInclTax()
                    || ($this->taxConfig->displaySalesSubtotalBoth() && $key == 1);
        }
    }

    private function getFixedTaxTotals($total)
    {
        $totalsReturn = [];
        $fullTaxInfo = $total->getFullTaxInfo();

        foreach ($fullTaxInfo as $taxLine) {
            $line = [];
            // in cases where Magento\Tax\Api\Data\OrderTaxDetailsInterface::getAppliedTaxes() is missing we need
            // to fall back on the reproduced process which has less info
            if (!isset($taxLine['title'])) {
                $line['label'] = isset($taxLine['label']) && $taxLine['label'] ? $taxLine['label'] : '';
                $line['amount'] = isset($taxLine['amount']) ? $taxLine['amount'] : '';
                $line['base_amount'] = isset($taxLine['base_amount']) ? $taxLine['base_amount'] : '';
            } else {
                $line['label'] = $taxLine['title'] . ' (' . (float)$taxLine['percent'] . '%):';
                $line['amount'] = $this->getAmountPrefix()
                    . $this->getOrder()->getOrderCurrency()->formatTxt($taxLine['tax_amount']);
                $line['base_amount'] = $this->getAmountPrefix()
                    . $this->getOrder()->getBaseCurrency()->formatTxt($taxLine['base_tax_amount']);
            }
            $totalsReturn[] = $line;
        }
        return $totalsReturn;
    }

    public function shouldDisplayBothCurrencies()
    {
        $enabled = $this->_scopeConfig->getValue(
            AbstractSalesDocument::XML_PATH_DISPLAYBOTH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getOrder()->getStoreId()
        );

        return $enabled
            && $this->getOrder()->getBaseCurrencyCode() !== $this->getOrder()->getOrderCurrencyCode();
    }

    /**
     * @param \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal $total
     *
     * @return bool
     */
    private function isTaxDisplayedWithGrandTotal(\Magento\Sales\Model\Order\Pdf\Total\DefaultTotal $total)
    {
        return $total->getSourceField() === 'grand_total' &&
            $this->taxConfig->displaySalesTaxWithGrandTotal(
                $this->getOrder()->getStoreId()
            );
    }

    /**
     * @param \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal $total
     *
     * @return bool
     */
    private function isFullTaxDisplayed(\Magento\Sales\Model\Order\Pdf\Total\DefaultTotal $total)
    {
        return $total->getSourceField() === 'tax_amount' &&
            $this->taxConfig->displaySalesFullSummary(
                $this->getOrder()->getStoreId()
            ) && !$this->taxConfig->displaySalesTaxWithGrandTotal(
                $this->getOrder()->getStoreId()
            );
    }
}
