<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fooman\OrderManager\Model;

class StateLookup
{
    /**
     * @var array
     */
    private $statusMap = [];

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory
     */
    private $status;

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $status
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $status
    ) {
        $this->status = $status;
    }

    /**
     * @param $status
     *
     * @return mixed
     */
    public function getStateForStatus($status)
    {
        if (!isset($this->statusMap[$status])) {
            $collection = $this->status->create();
            $collection->joinStates()->addFieldToFilter('main_table.status', $status)->setPageSize(1);
            $this->statusMap[$status] = $collection->getFirstItem()->getState();
        }
        return $this->statusMap[$status];
    }
}
