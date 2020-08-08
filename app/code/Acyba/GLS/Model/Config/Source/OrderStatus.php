<?php
/**
 * Source Model for configuration
 * Specifies available orders statuses
 **/

namespace Acyba\GLS\Model\Config\Source;

use \Magento\Framework\Option\ArrayInterface;
use \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;

class OrderStatus implements ArrayInterface
{
    protected $statusCollectionFactory;

    /**
     * OrderStatus constructor.
     * @param CollectionFactory $statusCollectionFactory
     */
    public function __construct(CollectionFactory $statusCollectionFactory)
    {
        $this->statusCollectionFactory = $statusCollectionFactory;
    }

    /*
      * Option getter
      * @return array
    */
    public function toOptionArray()
    {
        $options = $this->statusCollectionFactory->create()->toOptionArray();
        return $options;
    }
}
