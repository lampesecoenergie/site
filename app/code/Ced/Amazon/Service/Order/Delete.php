<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 12/9/19
 * Time: 7:12 PM
 */

namespace Ced\Amazon\Service\Order;

use Ced\Amazon\Api\Processor\BulkActionProcessorInterface;
use Ced\Amazon\Model\ResourceModel\Order\CollectionFactory;
use Ced\Amazon\Helper\Logger;

class Delete implements BulkActionProcessorInterface
{
    /**
     * @var \Ced\Amazon\Model\ResourceModel\Order\CollectionFactory
     */
    public $orderCollectionFactory;

    /**
     * @var \Ced\Amazon\Helper\Logger
     */
    public $logger;

    public function __construct(
        CollectionFactory $orderCollectionFactory,
        Logger $logger
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->logger = $logger;
    }

    public function process($ids)
    {
        try {
            $status = false;
            if (!empty($ids)) {
                /** @var \Ced\Amazon\Model\ResourceModel\Order\Collection $collection */
                $collection = $this->orderCollectionFactory->create()->addFieldToFilter('id', ['in' => $ids]);
                if (isset($collection) && $collection->getSize() > 0) {
                    $collection->walk('delete');
                    $status = true;
                }
            }
        } catch (\Exception $e) {
            $status = false;
            $this->logger->error(
                'Error in bulk order delete',
                [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]
            );
        }

        return $status;
    }
}