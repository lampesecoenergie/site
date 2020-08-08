<?php
namespace Fooman\PdfCustomiser\Block\Table;

use Magento\Framework\View\Element\Template;

/**
 * Class to determine how bundle and its children should get displayed
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class BundleHandler extends \Fooman\PdfCore\Block\Pdf\Block
{
    /**
     * @var \Fooman\PdfDesign\Model\TemplateFileDecider
     */
    private $templateFileDecider;

    /**
     * @var \Fooman\PdfCustomiser\Helper\BundleProductItem
     */
    protected $bundleProductItemHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    private $attributeFactory;

    public function __construct(
        Template\Context $context,
        \Fooman\PdfDesign\Model\TemplateFileDecider $templateFileDecider,
        \Fooman\PdfCustomiser\Helper\BundleProductItem $bundleProductItemHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        array $data = []
    ) {
        $this->templateFileDecider = $templateFileDecider;
        $this->bundleProductItemHelper = $bundleProductItemHelper;
        $this->productFactory = $productFactory;
        $this->attributeFactory = $attributeFactory;
        parent::__construct($context, $data);
    }

    // phpcs:ignore PSR2.Methods.MethodDeclaration -- Magento 2 core use
    public function _toHtml()
    {
        if ($this->bundleProductItemHelper->displayBundleChildrenAsLineItem($this->getOrderItem())) {
            $dataArray = [
                'printHeader' => false,
                'tableColumns' => $this->getData('table_columns'),
                'currency_code' => $this->getOrderCurrencyCode()
            ];
            if ($this->getBaseCurrencyCode()) {
                $dataArray['base_currency_code'] = $this->getBaseCurrencyCode();
            }

            $block = $this->getLayout()->createBlock(
                \Fooman\PdfCustomiser\Block\Table::class,
                'pdfcustomiser.bundlechildren' . uniqid('bundlechildren', true),
                ['data' => $dataArray]
            );
            $block->setTemplate($this->templateFileDecider->pick($this->getFoomanDesign(), 'table'));
            $style = $this->getStyling();
            $style['row'] = [
                'default' => 'border-bottom:0px none transparent;',
                'last' => 'border-bottom:0px none transparent;',
                'first' => 'border-bottom:0px none transparent;'
            ];
            $block->setStyling($style);
            $block->setCollection($this->getBundleChildren());
        } else {
            $block = $this->getLayout()->createBlock(
                \Fooman\PdfCustomiser\Block\Table\BundleExtras::class,
                'pdfcustomiser.bundleextras' . uniqid('pdfcustomiser.bundleextras', true),
                ['data' => ['order_item' => $this->getOrderItem(), 'item' => $this->getItem()]]
            );
            $block->setTemplate(
                $this->templateFileDecider->pick($this->getFoomanDesign(), 'bundleExtras')
            );
        }

        return $block->toHtml();
    }

    public function getChildItemsOnly()
    {
        return $this->getBundleChildren();
    }

    private function getBundleChildren()
    {
        $allChildren = $this->getOrderItem()->getChildrenItems();
        $allChildrenIds = [];
        $filteredObjects = [];
        $bundleToChildren = [];
        $lastLabel = false;
        $currentLabel = '';

        $orderItem = $this->getOrderItem();
        $item = $this->getItem();

        foreach ($allChildren as $orderChildItem) {
            $allChildrenIds[$orderChildItem->getId()] = '';
            $prodOpts = $orderChildItem->getProductOptions();
            if (isset($prodOpts['bundle_selection_attributes'])) {
                $currentLabel = json_decode($prodOpts['bundle_selection_attributes'], true)['option_label'];
                $allChildrenIds[$orderChildItem->getId()] = $currentLabel;
            }

            if ($lastLabel !== $currentLabel) {
                $orderChildItem->setFoomanRowLabel($currentLabel);
                $lastLabel = $currentLabel;
            }
            $this->prepareBundleChildItem($orderItem, $item, $orderChildItem, $orderChildItem);
            $filteredObjects[] = $orderChildItem;
            $bundleToChildren[$orderItem->getId()][] = $orderChildItem;
        }

        if ($item === $orderItem) {
            return $filteredObjects;
        }

        try {
            $salesObject = $this->getSalesObject($item);
        } catch (\UnexpectedValueException $e) {
            //returning the order bundle objects here is better than to fail completely
            return $filteredObjects;
        }

        $filteredObjects = [];
        $lastLabel = false;

        foreach ($salesObject->getAllItems() as $objectItem) {
            if (isset($allChildrenIds[$objectItem->getOrderItem()->getId()])) {
                $currentLabel = $allChildrenIds[$objectItem->getOrderItem()->getId()];

                if ($lastLabel !== $currentLabel) {
                    $objectItem->setFoomanRowLabel($currentLabel);
                    $lastLabel = $currentLabel;
                }
                $this->prepareBundleChildItem($orderItem, $item, $objectItem->getOrderItem(), $objectItem);
                $filteredObjects[] = $objectItem;
            }
            if (isset($bundleToChildren[$objectItem->getOrderItem()->getId()])) {
                foreach ($bundleToChildren[$objectItem->getOrderItem()->getId()] as $bundleChild) {
                    $this->addProductAttributeValues($orderItem, $bundleChild);
                    if ($salesObject instanceof \Magento\Sales\Api\Data\ShipmentInterface) {
                        $filteredObjects[] = $bundleChild;
                    }
                }
            }
        }

        return $filteredObjects;
    }

    private function getSalesObject($item)
    {
        $salesObject = null;
        if ($item instanceof \Magento\Sales\Api\Data\InvoiceItemInterface) {
            $salesObject = $item->getInvoice();
        } elseif ($item instanceof \Magento\Sales\Api\Data\CreditmemoItemInterface) {
            $salesObject = $item->getCreditmemo();
        } elseif ($item instanceof \Magento\Sales\Api\Data\ShipmentItemInterface) {
            $salesObject = $item->getShipment();
        } else {
            throw new \UnexpectedValueException('Unknown Sales Object Type');
        }

        return $salesObject;
    }

    public function prepareBundleChildItem($parentOrderItem, $parentItem, $orderItem, $item)
    {
        if ($this->bundleProductItemHelper->displayBundleChildrenAsLineItem($this->getOrderItem())) {
            $orderItem->unsPrice();
            $item->unsPrice();

            $parentQty = $parentItem->getQty() ? $parentItem->getQty() : $parentItem->getQtyOrdered();
            $multiplier = $this->findItemQtyMultiplier($parentOrderItem, $item);
            $orderItem->setQtyOrdered($parentQty * $multiplier);
            $item->setQty($parentQty * $multiplier);
        }
        $this->addProductAttributeValues($orderItem, $item);
    }

    private function findItemQtyMultiplier($parentOrderItem, $item)
    {
        $productOptions = $parentOrderItem->getProductOptions();
        foreach ($productOptions['bundle_options'] as $option) {
            foreach ($option['value'] as $chosenOption) {
                if ($chosenOption['title'] == $item->getName()) {
                    return $chosenOption['qty'];
                }
            }
        }
        throw new \Magento\Framework\Exception\LocalizedException(__('Was unable to determine Bundle Option Quantity'));
    }

    private function addProductAttributeValues(\Magento\Sales\Api\Data\OrderItemInterface $orderItem, $item)
    {

        $productAttributes = $this->getProductAttributes();
        if (count($productAttributes) == 0) {
            return;
        }

        $product = $this->productFactory->create()->load($orderItem->getProductId());

        if (!$product) {
            return;
        }

        foreach ($productAttributes as $productAttribute) {
            $value = '';
            if ($productAttribute === 'quantity_and_stock_status') {
                $extendedAttributes = $product->getExtensionAttributes();
                if ($extendedAttributes !== null) {
                    $stockItem = $extendedAttributes->getStockItem();
                    if ($stockItem) {
                        $value = $stockItem->getQty() . '<br/>';
                        $value .= $stockItem->getIsInStock() ? __('In Stock') : __('Out of Stock');
                    }
                }
            } else {
                /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
                $attribute = $this->attributeFactory
                    ->create()
                    ->loadByCode('catalog_product', $productAttribute);
                if ($attribute->getSourceModel()) {
                    $value = $attribute->getSource()->getOptionText($product->getData($productAttribute));
                } elseif ($attribute->getFrontendInput() === 'select') {
                    $value = $product->getAttributeText($productAttribute);
                } elseif ($attribute->getFrontendInput() === 'price') {
                    $value = ['render_as_currency' => true, 'value' => $product->getData($productAttribute)];
                } else {
                    $value = $product->getData($productAttribute);
                }
            }

            $orderItem->setData('product_' . $productAttribute, $value);
            $item->setData('product_' . $productAttribute, $value);
        }
    }

    private function getProductAttributes()
    {
        $productAttributes = [];
        $config = $this->getData('table_columns');
        if ($config) {
            foreach ($config as $column) {
                if (strpos($column['index'], 'product/') !== false) {
                    $productAttributes[] = str_replace('product/', '', $column['index']);
                }
            }
        }
        return $productAttributes;
    }
}
