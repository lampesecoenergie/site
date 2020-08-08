<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Model\Rule\Condition;

/**
 * @SuppressWarnings(PHPMD)
 * @codingStandardsIgnoreFile
 * @method string getAttribute()
 */
class Product extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * @var \Magento\Config\Model\Config\Source\YesnoFactory
     */
    protected $sourceYesNo;

    /**
     * @var \Magento\Cataloginventory\Model\Stock\ItemFactory
     */
    protected $stockItemFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $attributeSetCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Cataloginventory\Model\Source\Stock
     */
    protected $sourceStock;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $productType;

    /**
     * @var \Mirasvit\Feed\Model\Feed\Generator\Pattern\Product
     */
    protected $productPattern;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlManager;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $backendUrlManager;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $localeFormat;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Model\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Mirasvit\Feed\Export\Resolver\ProductResolver
     */
    protected $productResolver;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb
     */
    protected $resourceCollection;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $sourceStatus;

    public function __construct(
        \Magento\Config\Model\Config\Source\Yesno $systemConfigSourceYesno,
        \Magento\CatalogInventory\Model\Stock\ItemFactory $stockItemFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $entityAttributeSetCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Eav\Model\Config $config,
        \Magento\CatalogInventory\Model\Source\Stock $sourceStock,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $sourceStatus,
        \Magento\Catalog\Model\Product\Type $productType,
        \Magento\Framework\UrlInterface $urlManager,
        \Magento\Backend\Model\Url $backendUrlManager,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Framework\Registry $registry,
        \Mirasvit\Feed\Export\Resolver\ProductResolver $productResolver,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->sourceYesNo                   = $systemConfigSourceYesno;
        $this->stockItemFactory              = $stockItemFactory;
        $this->attributeSetCollectionFactory = $entityAttributeSetCollectionFactory;
        $this->productFactory                = $productFactory;
        $this->productCollectionFactory      = $productCollectionFactory;
        $this->eavConfig                     = $config;
        $this->sourceStock                   = $sourceStock;
        $this->productType                   = $productType;
        $this->sourceStatus                  = $sourceStatus;
        $this->urlManager                    = $urlManager;
        $this->backendUrlManager             = $backendUrlManager;
        $this->localeFormat                  = $localeFormat;
        $this->filesystem                    = $filesystem;
        $this->context                       = $context;
        $this->registry                      = $registry;
        $this->productResolver               = $productResolver;
        $this->productRepository             = $productRepository;
        $this->resource                      = $resource;
        $this->resourceCollection            = $resourceCollection;
        $this->assetRepo                     = $context->getAssetRepository();
        parent::__construct($context, $data);
    }

    protected $_entityAttributeValues = null;

    public function getAttributeObject()
    {
        try {
            $obj = $this->eavConfig
                ->getAttribute('catalog_product', $this->getAttribute());
        } catch (\Exception $e) {
            $obj = new \Magento\Framework\DataObject();
            $obj->setEntity($this->productFactory->create())
                ->setFrontendInput('text');
        }

        return $obj;
    }

    protected function _addSpecialAttributes(array &$attributes)
    {
        $attributes = array_merge($attributes, [
            'entity_id'        => __('Product ID'),
            'attribute_set_id' => __('Attribute Set'),
            'category_ids'     => __('Category'),
            'qty'              => __('Quantity'),
            'children_count'   => __('Amount of Children In Stock'),
            'type_id'          => __('Product Type'),
            'image'            => __('Base Image'),
            'thumbnail'        => __('Thumbnail'),
            'small_image'      => __('Small Image'),
            'image_size'       => __('Base Image Size (bytes)'),
            'thumbnail_size'   => __('Thumbnail Size (bytes)'),
            'small_image_size' => __('Small Image Size (bytes)'),
            'php'              => __('PHP Condition'),
            'is_in_stock'      => __('Stock Availability'),
            'manage_stock'     => __('Manage Stock'),
            'status_parent'    => __('Status(Parent Product)'),
            'is_salable'       => __('Is Salable'),
            'final_price'      => __('Final Price'),
        ]);
    }

    public function loadAttributeOptions()
    {
        $productAttributes = $this->productFactory->create()
            ->loadAllAttributes()
            ->getAttributesByCode();

        $attributes = [];
        foreach ($productAttributes as $attribute) {
            if (!$attribute->isAllowedForRuleCondition()) {
                continue;
            }
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        $this->_addSpecialAttributes($attributes);

        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    protected function _prepareValueOptions()
    {
        // Check that both keys exist. Maybe somehow only one was set not in this routine, but externally.
        $selectReady = $this->getData('value_select_options');
        $hashedReady = $this->getData('value_option');
        if ($selectReady && $hashedReady) {
            return $this;
        }

        // Get array of select options. It will be used as source for hashed options
        $selectOptions = null;
        if ($this->getAttribute() === 'attribute_set_id') {
            $entityTypeId  = $this->eavConfig
                ->getEntityType('catalog_product')->getId();
            $selectOptions = $this->attributeSetCollectionFactory->create()
                ->setEntityTypeFilter($entityTypeId)
                ->load()
                ->toOptionArray();
        } elseif ($this->getAttribute() === 'is_in_stock') {
            $selectOptions = [];
            $options       = $this->sourceStock->toOptionArray();
            foreach ($options as $option) {
                $selectOptions[$option['value']] = $option;
            }
        } elseif ($this->getAttribute() === 'is_salable') {
            $selectOptions = $this->sourceYesNo->toOptionArray();
        } elseif ($this->getAttribute() === 'type_id') {
            $selectOptions = $this->productType->getAllOptions();
        } elseif ($this->getAttribute() === 'status_parent') {
            $selectOptions = $this->sourceStatus->getAllOptions();
        } elseif ($this->getAttribute() === 'status') {
            $selectOptions = $this->sourceStatus->getAllOptions();
        } elseif (is_object($this->getAttributeObject())) {
            $attributeObject = $this->getAttributeObject();
            if ($attributeObject->usesSource()) {
                if ($attributeObject->getFrontendInput() == 'multiselect') {
                    $addEmptyOption = false;
                } else {
                    $addEmptyOption = true;
                }
                $selectOptions = $attributeObject->getSource()->getAllOptions($addEmptyOption);
            } elseif ($attributeObject->getFrontendInput() == "boolean") {
                $selectOptions = $this->sourceYesNo->toOptionArray();
            }
        }

        // Set new values only if we really got them
        if ($selectOptions !== null) {
            // Overwrite only not already existing values
            if (!$selectReady) {
                $this->setData('value_select_options', $selectOptions);
            }
            if (!$hashedReady) {
                $hashedOptions = [];
                foreach ($selectOptions as $o) {
                    if (is_array($o)) {
                        if (is_array($o['value'])) {
                            continue; // We cannot use array as index
                        }
                        $hashedOptions[$o['value']] = $o['label'];
                    }
                }
                $this->setData('value_option', $hashedOptions);
            }
        }

        return $this;
    }

    /**
     * Retrieve value by option.
     *
     * @param mixed $option
     *
     * @return string
     */
    public function getValueOption($option = null)
    {
        $this->_prepareValueOptions();

        return $this->getData('value_option' . (!is_null($option) ? '/' . $option : ''));
    }

    /**
     * Retrieve select option values.
     * @return array
     */
    public function getValueSelectOptions()
    {
        $this->_prepareValueOptions();

        return $this->getData('value_select_options');
    }

    /**
     * Retrieve after element HTML.
     * @return string
     */
    public function getValueAfterElementHtml()
    {
        $html = '';

        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
                $image = $this->assetRepo->getUrl('images/rule_chooser_trigger.gif');
                break;
        }

        if (!empty($image)) {
            $html
                = '<a href="javascript:void(0)" class="rule-chooser-trigger">
                <img src="' . $image . '" alt="" class="v-middle rule-chooser-trigger" title="'
                . __('Open Chooser') . '" /></a>';
        }

        return $html;
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);

        return $element;
    }

    public function collectValidatedAttributes($productCollection)
    {
        $attribute = $this->getAttribute();

        if (!in_array($attribute, ['entity_id', 'category_ids', 'qty', 'final_price', 'children_count', 'php', 'is_in_stock', 'manage_stock', 'status_parent', 'is_salable'])) {
            if ($attribute == 'image_size'
                || $attribute == 'small_image_size'
                || $attribute == 'thumbnail_size'
            ) {
                $attribute = str_replace('_size', '', $attribute);
            }

            $attributes             = $this->getRule()->getCollectedAttributes();
            $attributes[$attribute] = true;
            $this->getRule()->setCollectedAttributes($attributes);
            $productCollection->addAttributeToSelect($attribute, 'left');
        }

        return $this;
    }

    public function getInputType()
    {
        if ($this->getAttribute() === 'attribute_set_id' ||
            $this->getAttribute() === 'type_id' ||
            $this->getAttribute() === 'is_in_stock' ||
            $this->getAttribute() === 'status_parent' ||
            $this->getAttribute() === 'is_salable'
        ) {
            return 'select';
        }
        if ($this->getAttribute() === 'manage_stock') {
            return 'boolean';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'string';
        }

        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
                return 'select';

            case 'multiselect':
                return 'multiselect';

            case 'date':
                return 'date';

            case 'boolean':
                return 'boolean';

            default:
                return 'string';
        }
    }

    public function getValueElementType()
    {
        if ($this->getAttribute() === 'attribute_set_id' ||
            $this->getAttribute() === 'type_id' ||
            $this->getAttribute() === 'is_in_stock' ||
            $this->getAttribute() === 'status_parent' ||
            $this->getAttribute() === 'is_salable'
        ) {
            return 'select';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'text';
        }

        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
            case 'boolean':
                return 'select';

            case 'multiselect':
                return 'multiselect';

            case 'date':
                return 'date';

            default:
                return 'text';
        }
    }

    public function getValueElement()
    {
        $element = parent::getValueElement();
        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    $element->setImage($this->assetRepo->getUrl('images/grid-cal.gif'));
                    break;
            }
        }

        return $element;
    }

    public function getValueElementChooserUrl()
    {
        $url = false;
        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
                $url = 'catalog_rule/promo_widget/chooser/attribute/' . $this->getAttribute();
                if ($this->getJsFormObject()) {
                    $url .= '/form/' . $this->getJsFormObject();
                }
                break;
        }

        return $url !== false ? $this->backendUrlManager->getUrl($url) : '';
    }

    public function getExplicitApply()
    {
        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
            case 'php':
                return true;
        }
        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    return true;
            }
        }

        return false;
    }

    public function loadArray($arr)
    {
        $this->setAttribute(isset($arr['attribute']) ? $arr['attribute'] : false);
        $attribute = $this->getAttributeObject();

        if ($attribute && $attribute->getBackendType() == 'decimal') {
            if (isset($arr['value'])) {
                if (!empty($arr['operator'])
                    && in_array($arr['operator'], ['!()', '()'])
                    && false !== strpos($arr['value'], ',')
                ) {
                    $tmp = [];
                    foreach (explode(',', $arr['value']) as $value) {
                        $tmp[] = $this->localeFormat->getNumber($value);
                    }
                    $arr['value'] = implode(',', $tmp);
                } else {
                    $arr['value'] = $this->localeFormat->getNumber($arr['value']);
                }
            } else {
                $arr['value'] = false;
            }
            $arr['is_value_parsed'] = isset($arr['is_value_parsed'])
                ? $this->localeFormat->getNumber($arr['is_value_parsed']) : false;
        }

        return parent::loadArray($arr);
    }

    /**
     * Get children collection of the parent product
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return array
     */
    public function getChildrenInStock(\Magento\Framework\Model\AbstractModel $object)
    {
        $children = $object->getTypeInstance()->getChildrenIds($object->getId());
        $children = $this->productCollectionFactory->create()
            ->addFieldToFilter('entity_id', ['in' => $children[0]])
            ->joinField(
                'qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id = entity_id',
                '{{table}}.stock_id = 1',
                'left'
            );

        return $children;
    }

    /**
     * Validate product attrbute value for condition.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Catalog\Model\Product $object */
        $attrCode = $this->getAttribute();

        switch ($attrCode) {
            case 'entity_id':
                $value = $object->getEntityId();

                return $this->validateAttribute($value);
                break;

            case 'is_salable':
                $object = $this->productRepository->getById($object->getId());
                if ($object->getVisibility() == \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE) {
                    $parent = $this->productResolver->getParent($object);
                    if ($parent->getStatus() == 1 && $parent->getId() !== $object->getId()) {
                        $value = $parent->isSalable();
                    } else {
                        $value = null;
                    }
                } else {
                    $value = $object->isSalable();
                }
                $object->setIsSalable($value);

                return $this->validateAttribute($value);
                break;

            case 'status_parent':
                $status = $this->productResolver->getParent($object)->getStatus();

                return $this->validateAttribute($status);
                break;

            case 'image':
            case 'small_image':
            case 'thumbnail':

                $object = $this->productRepository->getById($object->getId());
                $value  = $object->getData($attrCode);

                if ('' === $value || 'no_selection' === $value) {
                    $value = null;
                }

                return $this->validateAttribute($value);
                break;

            case 'category_ids':
                $catIds   = array_merge($object->getAvailableInCategories(), $object->getCategoryIds());
                $catIds[] = 0; //required for validate products without category at all

                return $this->validateAttribute($catIds);
                break;

            case 'qty':
                if ($object->getTypeId() == 'configurable') {
                    $totalQty = 0;
                    $children = $this->getChildrenInStock($object);
                    foreach ($children as $child) {
                        # if product enabled
                        if ($child->getStatus() == 1) {
                            $totalQty += $child->getQty();
                        }
                    }

                    return $this->validateAttribute($totalQty);
                } else {
                    $stockItem = $this->stockItemFactory->create()->load($object->getId(), 'product_id');

                    return $this->validateAttribute($stockItem->getQty());
                }

                break;

            case 'final_price':
                $object = $this->productRepository->getById($object->getId());
                $value  = $object->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();

                return $this->validateAttribute($value);

                break;

            case 'children_count':
                $parent = $this->productResolver->getParent($object);
                if ($parent->getTypeId() == 'configurable') {
                    $stockQty           = 0;
                    $childrenCollection = $this->getChildrenInStock($parent);
                    if (count($childrenCollection) > 0) {
                        foreach ($childrenCollection as $child) {
                            if ($child->isSalable() === true) {
                                $stockQty++;
                            }
                        }
                    } else {
                        return false;
                    }

                    return $this->validateAttribute($stockQty);
                } else {
                    return true;
                }

                break;

            case 'is_in_stock':
                $stockItem   = $this->stockItemFactory->create()->load($object->getId(), 'product_id');
                $stockStatus = 0;
                if ($stockItem->getIsInStock()) {
                    $stockStatus = 1;
                }

                return $this->validateAttribute($stockStatus);
                break;

            case 'manage_stock':
                $stockItem = $this->stockItemFactory->create()->load($object->getId(), 'product_id');
                $m         = 0;
                if ($stockItem->getManageStock()) {
                    $m = 1;
                }

                return $this->validateAttribute($m);
                break;

            case 'image_size':
            case 'small_image_size':
            case 'thumbnail_size':
                $object    = $this->productRepository->getById($object->getId());
                $imageCode = str_replace('_size', '', $attrCode);

                $imagePath = $object->getData($imageCode);
                $path      = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath() . '/catalog/product' . $imagePath;

                $size = 0;
                if (file_exists($path) && is_file($path)) {
                    $size = filesize($path);
                }

                return $this->validateAttribute($size);
                break;

            case 'php':
                $object = $object->load($object->getId());
                extract($object->getData());
                $expr  = 'return ' . $this->getValue() . ';';
                $eval  = "eval";
                $value = $eval($expr);

                if ($this->getOperator() == '==') {
                    return $value;
                } else {
                    return !$value;
                }

                break;

            default:
                if (!isset($this->_entityAttributeValues[$object->getId()])) {
                    $ts   = microtime(true);
                    $attr = $object->getResource()->getAttribute($attrCode);

                    if ($attr && $attr->getBackendType() == 'datetime' && !is_int($this->getValue())) {
                        $this->setValue(strtotime($this->getValue()));
                        $value = strtotime($object->getData($attrCode));

                        return $this->validateAttribute($value);
                    }

                    if ($attr && $attr->getFrontendInput() == 'multiselect') {
                        if (!$object->hasData($attrCode)) {
                            $object->load($object->getId());
                        }

                        $value = $object->getData($attrCode);

                        $value = strlen($value) ? explode(',', $value) : [];

                        return $this->validateAttribute($value);
                    }

                    return parent::validate($object);
                } else {
                    $result       = false; // any valid value will set it to TRUE
                    $oldAttrValue = $object->hasData($attrCode) ? $object->getData($attrCode) : null;

                    foreach ($this->_entityAttributeValues[$object->getId()] as $storeId => $value) {
                        $attr = $object->getResource()->getAttribute($attrCode);
                        if ($attr && $attr->getBackendType() == 'datetime') {
                            $value = strtotime($value);
                        } elseif ($attr && $attr->getFrontendInput() == 'multiselect') {
                            $value = strlen($value) ? explode(',', $value) : [];
                        }

                        $object->setData($attrCode, $value);
                        $result |= parent::validate($object);

                        if ($result) {
                            break;
                        }
                    }

                    if (is_null($oldAttrValue)) {
                        $object->unsetData($attrCode);
                    } else {
                        $object->setData($attrCode, $oldAttrValue);
                    }

                    return (bool)$result;
                }

                break;
        }
    }

    public function getJsFormObject()
    {
        return 'rule_conditions_fieldset';
    }
}
