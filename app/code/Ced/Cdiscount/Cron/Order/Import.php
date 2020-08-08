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

namespace Ced\Cdiscount\Cron\Order;

class Import
{
    public $logger;
    public $config;

    /**
     * Import constructor.
     *
     * @param \Ced\Cdiscount\Helper\Order $order
     * @param \Ced\Cdiscount\Helper\Logger $logger
     */
    public function __construct(
        \Ced\Cdiscount\Helper\Order $order,
        \Ced\Cdiscount\Helper\Logger $logger,
        \Ced\Cdiscount\Helper\Config $config
    ) {
        $this->order = $order;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @return bool
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute()
    {
        if ($this->config->getOrderCronStatus() == true) {
            $this->logger->info('Fetching orders from Cdiscount.');
            $order = $this->order->importOrders();
            return $order;
        }
        return false;
    }
}
