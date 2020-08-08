<?php
namespace Fooman\PdfCustomiser\Block;

use \Magento\Framework\View\Element\Template\Context;

/**
 * Class to render a table of items
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Table extends \Fooman\PdfCore\Block\Pdf\Table
{
    /**
     * @var \Magento\GiftMessage\Api\OrderItemRepositoryInterface
     */
    protected $orderItemRepository;

    /**
     * @var \Fooman\PdfCustomiser\Helper\BundleProductItem
     */
    protected $bundleProductItemHelper;

    /**
     * @var \Fooman\PdfDesign\Model\TemplateFileDecider
     */
    private $templateFileDecider;

    public function __construct(
        Context $context,
        \Magento\GiftMessage\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Fooman\PdfCustomiser\Helper\BundleProductItem $bundleProductItemHelper,
        \Fooman\PdfDesign\Model\TemplateFileDecider $templateFileDecider,
        array $data = []
    ) {
        $this->orderItemRepository = $orderItemRepository;
        $this->bundleProductItemHelper = $bundleProductItemHelper;
        $this->templateFileDecider = $templateFileDecider;
        parent::__construct($context, $data);
    }

    public function hasExtras(\Magento\Framework\DataObject $item)
    {
        $item = $this->getOrderItem($item);

        // we want to display bundle products info as extras
        if ($this->bundleProductItemHelper->isItemBundleProduct($item)) {
            return true;
        }

        try {
            $this->orderItemRepository->get($item->getOrderId(), $item->getItemId());
            $hasGiftMessage = true;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $hasGiftMessage = false;
        }
        return ($this->itemHasProductOption($item) || $hasGiftMessage || !empty($item->getMpGiftWrapData()));
    }

    public function getExtras(\Magento\Framework\DataObject $item)
    {
        $html = '';
        $orderItem = $this->getOrderItem($item);
        if ($this->bundleProductItemHelper->isItemBundleProduct($orderItem)) {
            $bundleExtras = $this->getLayout()->createBlock(
                \Fooman\PdfCustomiser\Block\Table\BundleHandler::class,
                'pdfcustomiser.bundlehandler' . uniqid('pdfcustomiser.bundleextras', true),
                [
                    'data' => [
                        'order_item' => $orderItem,
                        'item' => $item,
                        'fooman_design' => $this->getFoomanDesign(),
                        'table_columns' => $this->getData('tableColumns'),
                        'styling' => $this->style,
                        'currency_code' => $this->getOrderCurrencyCode(),
                        'rtl' => $this->getData('rtl')
                    ]
                ]
            );
            $html .= $bundleExtras->toHtml();
        }

        $html .= $this->getExtraCellHtml($item);

        return $html;
    }

    public function getExtrasAsCollection(\Magento\Framework\DataObject $item)
    {
        $orderItem = $this->getOrderItem($item);
        if ($this->bundleProductItemHelper->isItemBundleProduct($orderItem)) {
            $bundleExtras = $this->getLayout()->createBlock(
                \Fooman\PdfCustomiser\Block\Table\BundleHandler::class,
                'pdfcustomiser.bundlehandler' . uniqid('pdfcustomiser.bundleextras', true),
                [
                    'data' => [
                        'order_item' => $orderItem,
                        'item' => $item,
                        'fooman_design'=> $this->getFoomanDesign()
                    ]
                ]
            );
            return $bundleExtras->getChildItemsOnly();
        }
        return [];
    }

    public function getExtraCellHtml(\Magento\Framework\DataObject $item)
    {
        $orderItem = $this->getOrderItem($item);
        $extras = $this->getLayout()->createBlock(
            \Fooman\PdfCustomiser\Block\Table\Extras::class,
            'pdfcustomiser.extras' . uniqid('pdfcustomiser.extras', true),
            ['data' => ['order_item' => $orderItem, 'item' => $item]]
        );
        $extras->setTemplate(
            $this->templateFileDecider->pick($this->getFoomanDesign(), 'extras')
        );
        return $extras->toHtml();
    }

    protected function getOrderItem($item)
    {
        if ($item instanceof \Magento\Sales\Api\Data\OrderItemInterface) {
            return $item;
        }
        return $item->getOrderItem();
    }

    /**
     * @param \Magento\Framework\DataObject $item
     *
     * @return bool
     */
    private function itemHasProductOption(\Magento\Framework\DataObject $item)
    {
        $hasOptions = false;
        $options = $item->getProductOptions();
        $arrayKeys = ['options', 'additional_options', 'attributes_info'];
        foreach ($arrayKeys as $key) {
            if (isset($options[$key])) {
                $hasOptions = true;
            }
        }
        return $hasOptions;
    }
}
