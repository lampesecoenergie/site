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
 * @package   Ced_RueDuCommerce
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RueDuCommerce\Ui\DataProvider\Profile;

use Ced\RueDuCommerce\Model\ProfileProduct;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\FilterBuilder;
use Ced\RueDuCommerce\Model\Profile;

/**
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
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->bookmark = $bookmark;
        $this->filterBuilder = $filterBuilder;
        $this->collection = $collectionFactory->create();
        $this->request = $context->getRequest();

        $profileId = $this->request->getParam('rueducommerce_profile_id');
        $bookmarks = $this->bookmark->create()
            ->getCollection()
            ->addFieldToFilter('namespace', ['eq' => 'sears_products_index']);
        if (isset($profileId) and !empty($profileId)) {
            foreach ($bookmarks as $bookmark) {
                if ($bookmark->getIdentifier() == 'current') {
                    $configValue = $bookmark->getConfig();
                    $configValue['current']['filters']['applied']['rueducommerce_profile_id'] = $profileId;
                    $bookmark->setConfig(json_encode($configValue));
                    $bookmark->save();
                }
            }
        } else {
            foreach ($bookmarks as $bookmark) {
                if ($bookmark->getIdentifier() == 'current') {
                    $configValue = $bookmark->getConfig();
                    if (isset($configValue['current']['filters']['applied']['rueducommerce_profile_id'])) {
                        unset($configValue['current']['filters']['applied']['rueducommerce_profile_id']);
                    }
                    $bookmark->setConfig(json_encode($configValue));
                    $bookmark->save();
                }
            }
        }

//        $this->addField('rueducommerce_profile_id');
        $this->addField('rueducommerce_product_status');
        $this->addField('rueducommerce_validation_errors');
        $this->addField('rueducommerce_feed_errors');
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
        $items = $this->getCollection()->toArray();
        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
    }
}
