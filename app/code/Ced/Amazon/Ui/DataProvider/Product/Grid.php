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

namespace Ced\Amazon\Ui\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\FilterBuilder;
use Ced\Amazon\Model\Profile;

/**
 * Class Grid
 */
class Grid extends \Magento\Ui\DataProvider\AbstractDataProvider
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

    /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection|void  */
    public $collection;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Framework\App\RequestInterface $request,
        \Ced\Amazon\Model\ResourceModel\Profile\CollectionFactory $profileFactory,
        CollectionFactory $collectionFactory,
        FilterBuilder $filterBuilder,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;

        $filters = $request->getParam('filters', ['store_id' => 0]);
        // TODO: check for single store mode
        $storeId = isset($filters['store_id']) ? $filters['store_id'] : 0;
        /** @var \Ced\Amazon\Model\ResourceModel\Profile\Collection $profile */
        $profile = $profileFactory->create()
            ->addFieldToFilter(
                \Ced\Amazon\Model\Profile::COLUMN_STATUS,
                ['eq' => \Ced\Amazon\Model\Source\Profile\Status::ENABLED]
            );
        $profileIds = $profile->getAllIds();

        $this->filterBuilder = $filterBuilder;
        $this->collection->setStoreId($storeId);

        $this->addField(\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_PROFILE_ID);
        $this->addField(\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_PRODUCT_STATUS);
        $this->addField(\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_VALIDATION_ERRORS);
        $this->addField(\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_FEED_ERRORS);
        $this->addFilter($this->filterBuilder->setField(\Ced\Amazon\Helper\Product::ATTRIBUTE_CODE_PROFILE_ID)
            ->setConditionType('in')
            ->setValue($profileIds)
            ->create());
        $this->addFilter($this->filterBuilder->setField('type_id')
            ->setConditionType('in')
            ->setValue(['simple', 'configurable'])
            ->create());
        $this->addFilter($this->filterBuilder->setField('visibility')
            ->setConditionType('in')
            ->setValue([1, 2, 3, 4])
            ->create());
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

    public function getMeta()
    {
        $meta = parent::getMeta();

        return $meta;
    }

    public function getData()
    {

        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }

        $data = parent::getData();

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($data),
        ];
    }
}
