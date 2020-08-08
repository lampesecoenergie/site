<?php
namespace Fooman\PdfCustomiser\Block;

// phpcs:disable Generic.Metrics.NestingLevel.TooHigh
/**
 * Parent class for all sales document pdfs
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
abstract class AbstractSalesDocument extends \Fooman\PdfCore\Block\Pdf\DocumentRenderer
{
    const XML_PATH_OWNERADDRESS = 'sales_pdf/all/allowneraddress';
    const XML_PATH_PRINTCOMMENTS = 'sales_pdf/all/page/allprintcomments';
    const XML_PATH_DISPLAYBOTH = 'sales_pdf/all/displayboth';

    const LAYOUT_HANDLE= 'fooman_pdfcustomiser';
    const PDF_TYPE = '';

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    protected $addressRenderer;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * @var \Fooman\PdfCore\Helper\Logo
     */
    protected $logoHelper;

    /**
     * @var string
     */
    protected $integratedLabelsConfigPath;

    /**
     * @var \Magento\GiftMessage\Api\OrderRepositoryInterface
     */
    protected $giftMessageOrderRepo;

    /**
     * @var \Fooman\PdfCore\Helper\ParamKey
     */
    protected $paramKeyHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    private $attributeFactory;

    /**
     * @var \Fooman\PdfCore\Model\IntegratedLabels\ContentFactory
     */
    private $integratedLabelsContentFactory;

    /**
     * @var \Fooman\PdfDesign\Model\DesignProvider
     */
    private $designProvider;

    /**
     * @var \Fooman\PdfDesign\Model\TemplateFileDecider
     */
    private $templateFileDecider;

    /**
     * @var \Fooman\PdfCore\Helper\Locale
     */
    private $localeHelper;

    /**
     * we probably need to use \Magento\Sales\Api\Data\OrderInterface here eventually
     * however the current code base around the address renderer does not use interfaces
     *
     * @return \Magento\Sales\Model\Order
     */
    abstract public function getOrder();

    /**
     * @return string
     */
    abstract public function getAddressesToDisplay();

    /**
     * @return mixed
     */
    abstract public function getSalesObject();

    /**
     * serialized config value for columns
     *
     * @return string
     */
    abstract public function getColumnConfig();

    /**
     * AbstractSalesDocument constructor.
     *
     * @param \Magento\Backend\Block\Template\Context               $context
     * @param \Magento\Framework\Filter\Input\MaliciousCode         $maliciousCode
     * @param \Fooman\PdfCore\Model\Template                        $template
     * @param \Magento\Sales\Model\Order\Address\Renderer           $addressRenderer
     * @param \Magento\Payment\Helper\Data                          $paymentHelper
     * @param \Fooman\PdfCore\Helper\Logo                           $logoHelper
     * @param \Fooman\PdfCore\Model\IntegratedLabels\ContentFactory $integratedLabelsContentFactory
     * @param \Magento\Catalog\Model\ProductFactory                 $productFactory
     * @param \Magento\Eav\Model\Entity\AttributeFactory            $attributeFactory
     * @param \Magento\GiftMessage\Api\OrderRepositoryInterface     $giftMessageOrderRepo
     * @param \Magento\Framework\App\AreaList                       $areaList
     * @param \Fooman\PdfCore\Helper\ParamKey                       $paramKeyHelper
     * @param \Fooman\PdfDesign\Model\DesignProvider                $designProvider
     * @param \Fooman\PdfDesign\Model\TemplateFileDecider           $templateFileDecider
     * @param \Fooman\PdfCore\Helper\Locale                         $localeHelper
     * @param array                                                 $data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Filter\Input\MaliciousCode $maliciousCode,
        \Fooman\PdfCore\Model\Template $template,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Fooman\PdfCore\Helper\Logo $logoHelper,
        \Fooman\PdfCore\Model\IntegratedLabels\ContentFactory $integratedLabelsContentFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\GiftMessage\Api\OrderRepositoryInterface $giftMessageOrderRepo,
        \Magento\Framework\App\AreaList $areaList,
        \Fooman\PdfCore\Helper\ParamKey $paramKeyHelper,
        \Fooman\PdfDesign\Model\DesignProvider $designProvider,
        \Fooman\PdfDesign\Model\TemplateFileDecider $templateFileDecider,
        \Fooman\PdfCore\Helper\Locale $localeHelper,
        array $data = []
    ) {
        $this->timezone = $context->getLocaleDate();
        $this->addressRenderer = $addressRenderer;
        $this->paymentHelper = $paymentHelper;
        $this->logoHelper = $logoHelper;
        $this->integratedLabelsContentFactory = $integratedLabelsContentFactory;
        $this->productFactory = $productFactory;
        $this->attributeFactory = $attributeFactory;
        $this->giftMessageOrderRepo = $giftMessageOrderRepo;
        $this->paramKeyHelper = $paramKeyHelper;
        $this->designProvider = $designProvider;
        $this->templateFileDecider = $templateFileDecider;
        $this->localeHelper = $localeHelper;
        parent::__construct($context, $maliciousCode, $template, $areaList, $data);
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        $storeId = $this->getSalesObject()->getStoreId();
        //we get a null storeId in case of a deleted store - fallback to default
        if ($storeId === null) {
            $store = $this->_storeManager->getDefaultStoreView();
            $storeId = $store->getId();
        }
        return $storeId;
    }

    /**
     * store owner address
     *
     * @return  string | bool
     * @access public
     */
    public function getOwnerAddress()
    {
        return $this->processCustomVars(
            $this->_scopeConfig->getValue(
                self::XML_PATH_OWNERADDRESS,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->getStoreId()
            ),
            $this->getTemplateVars()
        );
    }

    /**
     * variables to be made available in the template
     *
     * @return array
     */
    public function getTemplateVars()
    {
        return array_merge(
            $this->getDesignPickVars(),
            ['pdf_design' => $this->getDesign()]
        );
    }

    protected function getDesignPickVars()
    {
        return [
            'order' => $this->getOrder()
        ];
    }

    /**
     * @return \Fooman\PdfDesign\Model\Api\DesignInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDesign()
    {
        return $this->designProvider->getDesign($this->getStoreId(), $this->getDesignPickVars());
    }

    /**
     * @return string
     */
    protected function getTemplateText()
    {
        $templateText = sprintf(
            '{{layout handle="%s"',
            $this->getDesign()->getLayoutHandle(static::PDF_TYPE)
        );

        $templateVars = array_keys($this->getTemplateVars());
        foreach ($templateVars as $var) {
            $templateText .= ' '.$var.'=$'.$var;
        }
        $templateText .= '}}';
        return $templateText;
    }

    /**
     * @return bool
     */
    public function shouldDisplayBothAddresses()
    {
        return $this->getAddressesToDisplay() ===
        \Fooman\PdfCustomiser\Model\System\AddressOptions::BOTH_ADDRESSES;
    }

    /**
     * @return bool
     */
    public function shouldDisplayBillingAddress()
    {
        return $this->getAddressesToDisplay() ===
        \Fooman\PdfCustomiser\Model\System\AddressOptions::BILLING_ONLY;
    }

    /**
     * @return bool
     */
    public function shouldDisplayShippingAddress()
    {
        return $this->getAddressesToDisplay() ===
        \Fooman\PdfCustomiser\Model\System\AddressOptions::SHIPPING_ONLY;
    }

    /**
     * @return string
     */
    public function getBillingAddress()
    {
        return $this->filterAddress($this->addressRenderer->format($this->getOrder()->getBillingAddress(), 'pdf'));
    }

    /**
     * @return string
     */
    public function getShippingAddress()
    {
        if ($this->getOrder()->getIsVirtual()) {
            return '';
        }
        return $this->filterAddress($this->addressRenderer->format($this->getOrder()->getShippingAddress(), 'pdf'));
    }

    protected function getIntegratedLabelsConfigPath()
    {
        if (null === $this->integratedLabelsConfigPath) {
            $entityType = $this->getSalesObject()->getEntityType();
            $this->integratedLabelsConfigPath = 'sales_pdf/' . $entityType . '/' . $entityType . 'integratedlabels';
        }
        return $this->integratedLabelsConfigPath;
    }

    public function canApplyIntegratedLabelsContent()
    {
        $value = $this->getScopeConfig()->getValue(
            $this->getIntegratedLabelsConfigPath(),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return (($value != '0') && (null !== $value));
    }

    /**
     * @return \Fooman\PdfCore\Model\IntegratedLabels\Content
     */
    public function getIntegratedLabelsContent()
    {
        $value = $this->getScopeConfig()->getValue(
            $this->getIntegratedLabelsConfigPath(),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        $content = $this->integratedLabelsContentFactory->create();
        switch ($value) {
            case 'double':
                $content->setLeft($this->getBillingAddress());
                $content->setRight($this->getShippingAddress());
                break;
            case 'singlebilling':
                $content->setLeft($this->getBillingAddress());
                break;
            case 'singleshipping':
                $content->setLeft($this->getShippingAddress());
                break;
            case 'shipping-giftmessage':
                $content->setLeft($this->getShippingAddress());
                try {
                    $giftMessage = $this->giftMessageOrderRepo->get($this->getOrder()->getEntityId());
                    $content->setRight(
                        '<table width="70mm"><tr><td align="center">'.
                        $giftMessage->getMessage().
                        '</td></tr></table>'
                    );
                    // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    // Nothing to do - no associated gift message
                }
                break;
        }
        return $content;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function getShippingBlock()
    {
        /** @var \Fooman\PdfCustomiser\Block\Shipping $block */
        $block = $this->getLayout()->createBlock(\Fooman\PdfCustomiser\Block\Shipping::class, 'pdfcustomiser.shipping');
        $tracks = $this->getTracksCollection();
        if (!empty($tracks)) {
            $block->setTracks($tracks);
        }
        $block->setTemplate($this->templateFileDecider->pick($this->getDesign(), 'shipping'));
        $block->setShippingDescription($this->getOrder()->getShippingDescription());
        $block->setOrder($this->getOrder());
        $block->setSalesObject($this->getSalesObject());
        return $block->toHtml();
    }

    public function getTracksCollection()
    {
        return $this->getOrder()->getTracksCollection();
    }

    /**
     * @return string
     */
    public function getPaymentBlock()
    {
        try {
            $paymentBlock = $this->paymentHelper->getInfoBlock(
                $this->getOrder()->getPayment(),
                $this->getLayout()
            );
            $paymentBlock->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($this->getStoreId());
            $paymentBlock->setFoomanThemePath($this->design->getDesignTheme()->getThemePath());

            $paymentBlock->setTemplate(
                $this->templateFileDecider->pick($this->getDesign(), 'paymentDefault')
            );

            $html = $paymentBlock->toHtml();
        } catch (\Exception $e) {
            $html = '';
        }

        return $html;
    }

    /**
     * @return string
     */
    public function getTotalsBlock()
    {
        try {
            /** @var \Fooman\PdfCustomiser\Block\Totals $block */
            $block = $this->getLayout()->createBlock(\Fooman\PdfCustomiser\Block\Totals::class, 'pdfcustomiser.totals');
            $block->setOrder($this->getOrder());
            $block->setSalesObject($this->getSalesObject());
            $block->setDesign($this->getDesign());
            $block->setTemplate($this->templateFileDecider->pick($this->getDesign(), 'totals'));
            $html = $block->toHtml();
        } catch (\Exception $e) {
            $html = '';
        }
        return $html;
    }

    /**
     * @return \Fooman\PdfCustomiser\Block\Totals
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTotalsBlockObject()
    {
        /** @var \Fooman\PdfCustomiser\Block\Totals $block */
        $block = $this->getLayout()->createBlock(\Fooman\PdfCustomiser\Block\Totals::class, 'pdfcustomiser.totals');
        $block->setOrder($this->getOrder());
        $block->setSalesObject($this->getSalesObject());

        return $block;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function getTaxTableBlock()
    {
        /** @var \Fooman\PdfCustomiser\Block\TaxTable $block */
        $block = $this->getLayout()->createBlock(\Fooman\PdfCustomiser\Block\TaxTable::class, 'pdfcustomiser.taxtable');
        $block->setSalesObject($this->getSalesObject());
        $block->setTemplate($this->templateFileDecider->pick($this->getDesign(), 'taxTable'));
        return $block->toHtml();
    }

    /**
     * @return string|bool
     */
    public function getPrintComments()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_PRINTCOMMENTS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function getCommentsBlock()
    {
        $printCommentsConfig = $this->getPrintComments();
        if ($printCommentsConfig) {
            $comments = [];
            $salesObject = $this->getSalesObject();
            if ($salesObject instanceof \Magento\Sales\Model\Order) {
                switch ($printCommentsConfig) {
                    case \Fooman\PdfCustomiser\Model\System\PrintComments::PRINT_ALL:
                        $commentObject = $salesObject->getAllStatusHistory();
                        break;
                    case \Fooman\PdfCustomiser\Model\System\PrintComments::PRINT_FRONTEND_VISIBLE:
                        $commentObject = $salesObject->getVisibleStatusHistory();
                        break;
                    case \Fooman\PdfCustomiser\Model\System\PrintComments::PRINT_BACKEND_VISIBLE:
                        $allCommentObject = $salesObject->getAllStatusHistory();
                        $commentObject = [];
                        foreach ($allCommentObject as $history) {
                            if (!$history->getIsVisibleOnFront()) {
                                $commentObject[] = $history;
                            }
                        }
                        break;
                }

                if (!empty($commentObject)) {
                    foreach ($commentObject as $history) {
                        $comments[] = [
                            'date'    => $this->getFormattedDate($history->getCreatedAt()),
                            'label'   => $history->getStatusLabel(),
                            'comment' => $history->getComment()
                        ];
                    }
                }
            } else {
                if ($salesObject->getCommentsCollection()) {
                    switch ($printCommentsConfig) {
                        case \Fooman\PdfCustomiser\Model\System\PrintComments::PRINT_ALL:
                            $commentObject = $salesObject->getCommentsCollection();
                            break;
                        case \Fooman\PdfCustomiser\Model\System\PrintComments::PRINT_FRONTEND_VISIBLE:
                            $allCommentObject = $salesObject->getCommentsCollection();
                            foreach ($allCommentObject as $comment) {
                                if ($comment->getIsVisibleOnFront()) {
                                    $commentObject[] = $comment;
                                }
                            }
                            break;
                        case \Fooman\PdfCustomiser\Model\System\PrintComments::PRINT_BACKEND_VISIBLE:
                            $allCommentObject = $salesObject->getCommentsCollection();
                            $commentObject = [];
                            foreach ($allCommentObject as $comment) {
                                if (!$comment->getIsVisibleOnFront()) {
                                    $commentObject[] = $comment;
                                }
                            }
                            break;
                    }
                    if (!empty($commentObject)) {
                        foreach ($commentObject as $comment) {
                            if ($comment->getCreatedAt()) {
                                $date = $this->getFormattedDate($comment->getCreatedAt());
                            } else {
                                $date = '';
                            }
                            $comments[] = [
                                'date'    => $date,
                                'label'   => '',
                                'comment' => $comment->getComment()
                            ];
                        }
                    }
                }
            }
            if (!empty($comments)) {
                $block = $this->getLayout()->createBlock(
                    \Fooman\PdfCustomiser\Block\Comments::class,
                    'pdfcustomiser.comments' . uniqid(),
                    ['data' => ['comments' => $comments]]
                );
                $block->setTemplate($this->templateFileDecider->pick($this->getDesign(), 'comments'));
                return $block->toHtml();
            }
        }
        return '';
    }

    public function getGiftmessageBlock()
    {
        try {
            $giftMessage = $this->giftMessageOrderRepo->get($this->getOrder()->getEntityId());
            $block = $this->getLayout()->createBlock(
                \Fooman\PdfCustomiser\Block\Giftmessage::class,
                'pdfcustomiser.giftmessage' . uniqid(),
                ['data' => ['giftmessage' => $giftMessage]]
            );
            $block->setTemplate($this->templateFileDecider->pick($this->getDesign(), 'giftmessage'));
            return $block->toHtml();
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            //Nothing to do - no associated gift message
        }
        return '';
    }

    public function getIconsBlock()
    {
        $block = $this->getLayout()->createBlock(
            \Fooman\PdfDesign\Block\DesignOne\Icons::class,
            'pdcustomiser.designone_icons' . uniqid(),
            ['data' => ['storeId' => $this->getSalesObject()->getStoreId()]]
        );
        $block->setTemplate($this->templateFileDecider->pick($this->getDesign(), 'icons'));
        $block->setDesign($this->getDesign());
        return $block->toHtml();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function getLogoBlock()
    {
        $block = $this->getLayout()->createBlock(
            \Fooman\PdfCore\Block\Pdf\Template\Logo::class,
            'pdfcore.logo' . uniqid(),
            ['data' => ['storeId' => $this->getSalesObject()->getStoreId()]]
        );
        $block->setTemplate($this->templateFileDecider->pick($this->getDesign(), 'logo'));

        return $block->toHtml();
    }

    /**
     * @param array $styling
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function getItemsBlock($styling = [])
    {
        $dataArray = [
            'tableColumns' => $this->getTableColumns(),
            'currency_code' => $this->getOrder()->getOrderCurrencyCode(),
            'rtl' => $this->localeHelper->isRightToLeftByStoreId($this->getOrder()->getStoreId())
        ];
        if ($this->shouldDisplayBothCurrencies()) {
            $dataArray['base_currency_code']= $this->getOrder()->getBaseCurrencyCode();
        }

        $block = $this->getLayout()->createBlock(
            \Fooman\PdfCustomiser\Block\Table::class,
            'pdfcustomiser.items' . uniqid(),
            ['data' => $dataArray]
        );
        $block->setTemplate($this->templateFileDecider->pick($this->getDesign(), 'table'));
        $block->setFoomanDesign($this->getDesign());
        $block->setStyling($styling);
        $block->setCollection($this->getVisibleItems());
        return $block->toHtml();
    }

    /**
     * get line items to display
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVisibleItems()
    {
        $items = [];
        $allItems = $this->getSalesObject()->getItems();
        if ($allItems) {
            foreach ($allItems as $item) {
                if ($this->shouldDisplayItem($item)) {
                    $items[] = $this->prepareItem($item);
                }
            }
        }
        if ($this->getSortColumnsBy()) {
            uasort($items, [$this, 'sort']);
        }

        return $items;
    }

    protected function sort($a, $b)
    {
        $sortAscending = true;
        $sortField = str_replace('/', '_', $this->getSortColumnsBy());
        $firstValue = $a->getData($sortField) ?: $a->getDataUsingMethod($sortField);
        $secondValue = $b->getData($sortField) ?: $b->getDataUsingMethod($sortField);

        if ($sortAscending) {
            return strnatcmp($firstValue, $secondValue);
        }
        return strnatcmp($secondValue, $firstValue);
    }

    protected function getSortColumnsBy()
    {
        return false;
    }

    /**
     * We generally don't want to display subitems
     *
     * @param $item
     *
     * @return bool
     */
    public function shouldDisplayItem($item)
    {
        $orderItem = $item->getOrderItem();
        return !$orderItem->getParentItemId();
    }

    /**
     * Remove some fields on bundles
     *
     * @param $item
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareItem($item)
    {
        $orderItem = $item->getOrderItem();
        $this->addProductAttributeValues($orderItem, $item);

        if ($orderItem->getProductType() == \Magento\Bundle\Model\Product\Type::TYPE_CODE
            && $orderItem->isChildrenCalculated()) {
            $item->unsRowTotal();
            $item->unsRowTotalInclTax();
            $item->unsTaxAmount();
        }

        return $item;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @param                                            $item
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addProductAttributeValues(\Magento\Sales\Api\Data\OrderItemInterface $orderItem, $item)
    {

        $productAttributes = $this->getProductAttributes();
        if (count($productAttributes) === 0) {
            return;
        }

        if ($orderItem->getProductType()
            === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
        ) {
            //we want to load the attribute of the simple product if part of a configurable
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productFactory->create()->loadByAttribute(
                'sku',
                $orderItem->getProductOptionByCode('simple_sku')
            );
            $loadedByAttribute = true;
        } else {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productFactory->create()->load($orderItem->getProductId());
            $loadedByAttribute = false;
        }

        if (!$product) {
            return;
        }

        foreach ($productAttributes as $productAttribute) {
            $value = '';
            if ($productAttribute === 'quantity_and_stock_status') {
                if ($loadedByAttribute) {
                    //loadByAttribute() misses the after load plugin to populate inventory data
                    $product->load($product->getId());
                }
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
                    $value = ['render_as_currency'=> true, 'value'=>$product->getData($productAttribute)];
                } else {
                    $value = $product->getData($productAttribute);
                }
            }

            $orderItem->setData('product_' . $productAttribute, $value);
            $item->setData('product_' . $productAttribute, $value);
        }
    }

    /**
     * @return array
     */
    protected function getProductAttributes()
    {
        $productAttributes = [];
        $config = $this->getColumnConfig();
        if ($config) {
            if (is_string($config)) {
                $config = json_decode($config, true);
            }
            foreach ($config as $column) {
                if (strpos($column['columntype'], 'product/') !== false) {
                    $productAttributes[] = str_replace('product/', '', $column['columntype']);
                }
            }
        }
        return $productAttributes;
    }

    /**
     * converts pipe character to linebreak
     * removes empty lines
     *
     * @param string $input
     *
     * @return string
     */
    protected function filterAddress($input)
    {
        $input = $this->escapeHtml($input);
        $input = str_replace(['|', PHP_EOL], '<br/>', $input);
        return preg_replace('/(<br\s*\/?>\s*)+/', '<br/>', $input);
    }

    /**
     * convert pdf row separator into proper linebreaks
     *
     * @param string $input
     *
     * @return string
     */
    protected function filterPaymentBlock($input)
    {
        return str_replace('{{pdf_row_separator}}', '<br/>', $input);
    }

    /**
     * @param string $createdAt
     *
     * @return string
     */
    public function getFormattedDate($createdAt)
    {
        return $this->getFormattedDateAndTime($createdAt, \IntlDateFormatter::MEDIUM, false);
    }

    /**
     * @param string $createdAt
     * @param int  $format
     * @param bool $showTime
     *
     * @return string
     */
    public function getFormattedDateAndTime($createdAt, $format = \IntlDateFormatter::MEDIUM, $showTime = true)
    {
        $orderTimeZone = $this->getScopeConfig()->getValue(
            $this->timezone->getDefaultTimezonePath(),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getOrder()->getStoreId()
        );
        return $this->formatDate(
            $createdAt,
            $format,
            $showTime,
            $orderTimeZone
        );
    }

    /**
     * prepare column config value for use in table constructor
     *
     * @return array
     */
    public function getTableColumns()
    {
        $return = [];
        $config = $this->getColumnConfig();
        if ($config) {
            if (is_string($config)) {
                $config = json_decode($config, true);
            }
            foreach ($config as $column) {
                $currentColumn = [];
                $currentColumn['index'] = $column['columntype'];
                if (isset($column['width']) && $column['width'] > 0) {
                    $currentColumn['width'] = $column['width'];
                }
                if (isset($column['title'])) {
                    $currentColumn['title'] = $column['title'];
                }
                if (isset($column['align'])) {
                    $currentColumn['align'] = $column['align'];
                }
                $return[] = $currentColumn;
            }
        }
        return $return;
    }

    /**
     * @return bool
     */
    public function isLogoOnRight()
    {
        return $this->logoHelper->isLogoOnRight();
    }

    /**
     * @deprecated
     * @see \Fooman\PdfCustomiser\Model\Api\DesignInterface::getItemStyling()
     * @return array
     */
    public function getDefaultItemStyling()
    {
        return $this->getDesign()->getItemStyling();
    }

    public function getIncrement()
    {
        return $this->getSalesObject()->getIncrementId();
    }

    public function processCustomVars($input, $templateVars)
    {

        /** @var $this->template \Fooman\PdfCore\Model\Template */
        $this->template->setArea(\Magento\Framework\App\Area::AREA_FRONTEND);

        $this->template->setTemplateText($this->maliciousCode->filter($input));

        $this->template->setVars($templateVars);

        $this->template->setDesignConfig(
            [
                'store' => $this->getStoreId(),
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND
            ]
        );

        return $this->template->processTemplate();
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function getEncodedParams(array $params)
    {
        return $this->paramKeyHelper->getEncodedParams($params);
    }

    public function shouldDisplayBothCurrencies()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAYBOTH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    protected function getFooterLayoutHandle()
    {
        return $this->getDesign()->getFooterLayoutHandle();
    }
}
