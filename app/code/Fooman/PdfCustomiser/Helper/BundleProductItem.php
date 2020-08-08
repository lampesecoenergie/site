<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fooman\PdfCustomiser\Helper;

/**
 * Helper to handle bundle items
 */
class BundleProductItem
{
    const XML_PATH_FIXED_BUNDLES_AS_LINEITEMS = 'sales_pdf/all/default_bundle_item_display';

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface  $priceCurrency
     * @param \Magento\Framework\Escaper                         $escaper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->escaper = $escaper;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param  string $value
     *
     * @return string
     * @deprecated
     * @see \Fooman\PdfCustomiser\Block\Table\BundleExtras
     */
    protected function getFormattedPrice($value)
    {
        return $this->priceCurrency->format($value, false, null);
    }

    /**
     * @param  \Magento\Sales\Api\Data\OrderItemInterface $item
     *
     * @return boolean
     */
    public function isItemBundleProduct(\Magento\Sales\Api\Data\OrderItemInterface $item)
    {
        return ($item->getProductType() == \Magento\Bundle\Model\Product\Type::TYPE_CODE);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     *
     * @return bool
     */
    public function displayBundleChildrenAsLineItem(\Magento\Sales\Api\Data\OrderItemInterface $item)
    {
        return $item->isChildrenCalculated() ||
            !$this->scopeConfig->isSetFlag(
                self::XML_PATH_FIXED_BUNDLES_AS_LINEITEMS,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $item->getStoreId()
            );
    }

    /**
     * @param  \Magento\Sales\Api\Data\OrderItemInterface $item
     *
     * @return string
     * @deprecated
     * @see \Fooman\PdfCustomiser\Block\Table\BundleExtras
     */
    public function getBundleProductExtrasContent(\Magento\Sales\Api\Data\OrderItemInterface $item)
    {
        $html = '';
        $productOptions = $item->getProductOptions();
        if (!$productOptions) {
            return $html;
        }
        if (!isset($productOptions['bundle_options'])) {
            return $html;
        }
        foreach ($productOptions['bundle_options'] as $bundleOption) {
            $html .= $this->escaper->escapeHtml($bundleOption['label']) . '<br/>';
            foreach ($bundleOption['value'] as $value) {
                $html .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;' . __('Title:') . '</b> ' .
                    $this->escaper->escapeHtml($value['title']) . '<br/>';
                $html .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;' . __('Qty:') . '</b> ' .
                    $this->escaper->escapeHtml($value['qty']) . '<br/>';
                $html .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;' . __('Value:') . '</b> ' .
                    $this->escaper->escapeHtml($this->getFormattedPrice($value['price'])) . '<br/>';
            }
            $html .= '<br/>';
        }
        return $html;
    }
}
