<?php
namespace Fooman\PdfCustomiser\Block;

/**
 * Block to display breakdown of taxes
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class TaxTable extends \Fooman\PdfCore\Block\Pdf\PdfAbstract
{
    const XML_PATH_DISPLAY_TAX_SUMMARY = 'sales_pdf/all/alltaxsummary';

    // phpcs:ignore PSR2.Classes.PropertyDeclaration
    protected $_template = 'Fooman_PdfCustomiser::pdf/taxtable.phtml';

    private $taxHelper;
    private $priceCurrency;
    private $scopeConfig;
    private $taxOrderItemCollectionFactory;

    private $accumulatedTaxes = [];
    private $unallocatedTotals = [];

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Tax\Helper\Data $taxHelper,
        \Fooman\PdfCustomiser\Model\ResourceModel\Order\Tax\Item\CollectionFactory $taxOrderItemCollectionFactory,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->taxHelper = $taxHelper;
        $this->priceCurrency = $priceCurrency;
        $this->taxOrderItemCollectionFactory = $taxOrderItemCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getItems()
    {
        $output = [];
        if (!$this->scopeConfig->isSetFlag(
            self::XML_PATH_DISPLAY_TAX_SUMMARY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getSalesObject()->getStoreId()
        )) {
            return $output;
        }
        $this->accumulatedTaxes = [];
        foreach ($this->getSalesObject()->getAllItems() as $item) {
            if ($item instanceof \Magento\Sales\Api\Data\OrderItemInterface) {
                $orderItem = $item;
            } else {
                $orderItem = $item->getOrderItem();
            }
            $taxItems = $this->getTaxItems($orderItem);
            if (!empty($taxItems)) {
                foreach ($taxItems as $taxItem) {
                    $this->aggregateTaxFromTaxItem($taxItem->getTaxPercent(), $taxItem, $orderItem);
                }
            }
        }

        $taxes = $this->taxHelper->getCalculatedTaxes($this->getSalesObject());
        $this->unallocatedTotals = [];
        $this->unallocatedTotals[] = new \Magento\Framework\DataObject(
            [
                'base_row_total' => $this->getSalesObject()->getBaseShippingAmount(),
                'base_tax_amount' => $this->getSalesObject()->getBaseShippingTaxAmount()
            ]
        );

        if (!empty($taxes)) {
            foreach ($taxes as $tax) {
                $this->checkIfunallocatedApplies($tax);
                $tax['base_tax_basis'] = $this->formatCurrency(
                    $this->accumulatedTaxes[$tax['percent']]['base_tax_basis']
                );
                $tax['base_subtotal'] = $this->formatCurrency(
                    $this->accumulatedTaxes[$tax['percent']]['base_tax_basis'] + $tax['base_tax_amount']
                );
                $tax['base_tax_amount'] = $this->formatCurrency(
                    $tax['base_tax_amount']
                );
                $output[] = $tax;
            }
        }
        if (!empty($this->unallocatedTotals)) {
            foreach ($this->unallocatedTotals as $total) {
                $this->aggregateTax('0', $total);
            }
            $output[] = [
                'percent' => 0,
                'base_tax_basis' => $this->formatCurrency($this->accumulatedTaxes[0]['base_tax_basis']),
                'base_tax_amount' => $this->formatCurrency($this->accumulatedTaxes[0]['base_tax_amount']),
                'base_subtotal' => $this->formatCurrency(
                    $this->accumulatedTaxes[0]['base_tax_basis'] + $this->accumulatedTaxes[0]['base_tax_amount']
                )
            ];
        }

        return $output;
    }

    private function getTaxItems($orderItem)
    {
        return $this->taxOrderItemCollectionFactory->create()->getTaxItemsByItemId($orderItem->getItemId());
    }

    /**
     * Magento does not explicitly keep track of the tax rate of custom totals
     * we re-calculate against the existing tax rates and match based on proximity
     * due to unknown possible rounding
     * @param array $rate
     */
    private function checkIfunallocatedApplies($rate)
    {
        foreach ($this->unallocatedTotals as $key => $total) {
            $toCheck = $total->getBaseRowTotal() * ($rate['percent']/100);
            $diff = abs($toCheck - $total->getBaseTaxAmount());
            if ($diff < 0.02) {
                $this->aggregateTax($rate['percent'], $total);
                unset($this->unallocatedTotals[$key]);
            }
        }
    }

    private function formatCurrency($amount)
    {
        return $this->priceCurrency->format(
            $amount,
            null,
            null,
            null,
            $this->getSalesObject()->getBaseCurrencyCode()
        );
    }

    private function aggregateTax($rate, $item)
    {
        if (isset($this->accumulatedTaxes[$rate])) {
            $this->accumulatedTaxes[$rate]['base_tax_basis'] += $this->sumTaxBasis($item);
            $this->accumulatedTaxes[$rate]['base_tax_amount'] += $item->getBaseTaxAmount();
        } else {
            $this->accumulatedTaxes[$rate]['base_tax_basis'] = $this->sumTaxBasis($item);
            $this->accumulatedTaxes[$rate]['base_tax_amount'] = $item->getBaseTaxAmount();
        }
    }

    private function aggregateTaxFromTaxItem($rate, $item, $orderItem)
    {
        if (isset($this->accumulatedTaxes[$rate])) {
            $this->accumulatedTaxes[$rate]['base_tax_basis'] += $orderItem->getBaseRowTotal();
            $this->accumulatedTaxes[$rate]['base_tax_amount'] += $item->getBaseAmount() - $item->getRealBaseAmount();
        } else {
            $this->accumulatedTaxes[$rate]['base_tax_basis'] = $orderItem->getBaseRowTotal();
            $this->accumulatedTaxes[$rate]['base_tax_amount'] = $item->getBaseAmount() - $item->getRealBaseAmount();
        }
    }

    private function sumTaxBasis($item)
    {
        return $item->getBaseRowTotal()
            - $item->getBaseDiscountAmount()
            + $item->getBaseDiscountTaxCompensationAmount();
    }
}
