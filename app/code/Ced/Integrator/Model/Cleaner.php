<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Integrator
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Model;

class Cleaner
{
    /** @var ResourceModel\Log\CollectionFactory */
    public $logCollectionFactory;

    /** @var \Ced\Integrator\Helper\Logger */
    public $logger;

    public function __construct(
        \Ced\Integrator\Model\ResourceModel\Log\CollectionFactory $log,
        \Ced\Integrator\Helper\File\Logger $logger
    ) {
        $this->logCollectionFactory = $log;
        $this->logger = $logger;
    }
    
    public function clean($from = null, $to = null)
    {
        /** @var \Ced\Integrator\Model\ResourceModel\Log\Collection $records */
        $records = $this->logCollectionFactory->create();
        if (empty($from)) {
            $from = date('Y-m-d H:i:s', strtotime('-7 days'));
        }

        if (empty($to)) {
            $to = date('Y-m-d H:i:s', strtotime('now'));
        }

        $records = $records->addFieldToFilter(
            'datetime',
            [
                'from' => $from,
                'to' => $to,
                'date' => true,
            ]
        );

        $size = $records->getSize();
        if ($size > 0) {
            $records->walk('delete');
            $this->logger->addNotice("{$size} logs cleared from db.");
            return true;
        }

        return false;
    }
}
