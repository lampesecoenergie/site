<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Ui\DataProvider\Profile;

use Ced\Amazon\Model\ProfileProduct;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\FilterBuilder;
use Ced\Amazon\Model\Profile;

/**
 * TODO: recheck usability
 * Class Products
 */
class Products extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    public $addFieldStrategies;

    /**
     * @var array
     */
    public $addFilterStrategies;

    /**
     * @var FilterBuilder
     */
    public $filterBuilder;

    /**
     * @var Profile
     */
    public $profile;

    /**
     * @var \Magento\Ui\Model\Bookmark
     */
    public $bookmark;

    public $request;

    public $category;

    /**
     * JetProduct constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param FilterBuilder $filterBuilder
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Backend\App\Action\Context $context,
        CollectionFactory $collectionFactory,
        FilterBuilder $filterBuilder,
        \Magento\Ui\Model\BookmarkFactory $bookmark,
        \Ced\Amazon\Model\Source\Category $category,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->category = $category;
        $this->bookmark = $bookmark;
        $this->filterBuilder = $filterBuilder;
        $this->collection = $collectionFactory->create();
        //TODO: views can be created for each profile
        //$storeId = $context->getRequest()->getParam('store_id', 0);
        //$this->collection->setStoreId($storeId);

        $this->addField(\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_PROFILE_ID);
        $this->addField(\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_PRODUCT_STATUS);
        $this->addField(\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_VALIDATION_ERRORS);
        $this->addField(\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_FEED_ERRORS);

        $this->addFilter($this->filterBuilder->setField('type_id')
            ->setConditionType('in')
            ->setValue(['simple', 'configurable'])
            ->create());

        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }

    /**
     * @param \Magento\Framework\Api\Filter $filter
     * @return void
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        } else {
            parent::addFilter($filter);
        }
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->getCollection();
        $collection->addCategoryIds();
        /** @var \Magento\Catalog\Model\Product $item */
        foreach ($collection as &$item) {
            $value = implode(",", $this->category->getCategoryNames($item->getCategoryIds()));
            $item['category'] = $value;
        }

        $items = $collection->toArray();

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
    }

    public function getCategoryName($categoryId)
    {

    }
}
