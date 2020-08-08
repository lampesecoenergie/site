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
 * @package   Ced_Cdiscount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Cdiscount\Ui\DataProvider\Cron;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Cron\Model\ResourceModel\Schedule\Collection;

/**
 * Class DataProvider
 *
 * @package Ced\Cdiscount\Ui\DataProvider\Cron
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var $collection
     */
    public $collection;

    /**
     * @var $addFieldStrategies
     */
    public $addFieldStrategies;

    /**
     * @var $addFilterStrategies
     */
    public $addFilterStrategies;

    /**
     * DataProvider constructor.
     *
     * @param string     $name
     * @param string     $primaryFieldName
     * @param string     $requestFieldName
     * @param Collection $collectionFactory
     * @param array      $addFieldStrategies
     * @param array      $addFilterStrategies
     * @param array      $meta
     * @param array      $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collectionFactory,
        $addFieldStrategies = [],
        $addFilterStrategies = [],
        $meta = [],
        $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory/*->addFieldToFilter(['job_code'], [[ 'like' => "%ced_%"]])*/;
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        $items = $this->getCollection();
        return $items;
    }
}
