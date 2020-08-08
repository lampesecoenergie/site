<?php
namespace Fooman\PdfCore\Block\System\Config;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Columns extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var \Fooman\PdfCore\Model\Config\Source\ProductAttributes
     */
    protected $productAttributeSource;

    protected $excludes = [];

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Fooman\PdfCore\Model\Config\Source\ProductAttributes $productAttributeSource,
        array $data = []
    ) {
        $this->productAttributeSource = $productAttributeSource;
        if (isset($data['excludes'])) {
            $this->excludes = $data['excludes'];
        }
        parent::__construct($context, $data);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    // phpcs:ignore PSR2.Methods.MethodDeclaration -- Magento 2 Core use
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getColumns());
        }
        return parent::_toHtml();
    }

    protected function getColumns()
    {
        return [
            ['label' => __('Item Attributes'), 'value' => $this->getItemColumns()],
            ['label' => __('Product Attributes'), 'value' => $this->getProductColumns()]
        ];
    }

    public function getItemColumns()
    {
        $allColumns = [
            ['value' => 'sku', 'label' => __('Sku')],
            ['value' => 'name', 'label' => __('Name')],
            ['value' => 'position', 'label' => __('Position')],
            ['value' => 'price', 'label' => __('Price')],
            ['value' => 'priceWithWeee', 'label' => __('Price with FPT')],
            ['value' => 'originalPrice', 'label' => __('Original Price')],
            ['value' => 'qty', 'label' => __('Qty')],
            ['value' => 'qtyOrdered', 'label' => __('Order Qty')],
            ['value' => 'qtyBackOrdered', 'label' => __('Back Ordered Qty')],
            ['value' => 'qtyDetails', 'label' => __('Qty Details')],
            ['value' => 'subtotal', 'label' => __('Subtotal')],
            ['value' => 'subtotalExcl', 'label' => __('Subtotal (Excl.)')],
            ['value' => 'subtotalWithWeee', 'label' => __('Subtotal with FPT')],
            ['value' => 'discount', 'label' => __('Discount')],
            ['value' => 'rowtotal', 'label' => __('Row Total')],
            ['value' => 'tax', 'label' => __('Tax')],
            ['value' => 'productWeee', 'label' => __('Fixed Product Taxes')],
            ['value' => 'taxPercentage', 'label' => __('Tax Percentage')],
            ['value' => 'image', 'label' => __('Product Image')],
            ['value' => 'barcode', 'label' => __('Sku Barcode')],
            ['value' => 'weight', 'label' => __('Weight')],
            ['value' => 'rowWeight', 'label' => __('Row Weight')],
            ['value' => 'checkbox', 'label' => __('Checkbox')]
        ];
        if (!empty($this->excludes)) {
            foreach ($allColumns as $key => $column) {
                if (in_array($column['value'], $this->excludes)) {
                    unset($allColumns[$key]);
                }
            }
        }
        return $allColumns;
    }

    protected function getProductColumns()
    {
        return $this->productAttributeSource->toOptionArray();
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
