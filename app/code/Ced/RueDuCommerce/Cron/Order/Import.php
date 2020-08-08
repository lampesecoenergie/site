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

namespace Ced\RueDuCommerce\Cron\Order;

class Import
{
    public $logger;

    /**
     * Import constructor.
     *
     * @param \Ced\RueDuCommerce\Helper\Order  $order
     * @param \Ced\RueDuCommerce\Helper\Logger $logger
     */
    public function __construct(
        \Ced\RueDuCommerce\Helper\Order $order,
        \Ced\RueDuCommerce\Helper\Logger $logger,
        \Ced\RueDuCommerce\Helper\Config $config
    ) {
        $this->order = $order;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function execute()
    {
        try {
            $orderCron = $this->config->getOrderCron();
            if ($orderCron == '1') {
                $this->logger->info('Order Fetch Cron Enable', ['path' => __METHOD__, 'Cron Status' => 'Enable']);
                $order = $this->order->importOrders();
                $this->logger->info('Order Fetch Cron Response', ['path' => __METHOD__, 'OrderFetchReponse' => var_export($order)]);
                return $order;
            } else {
                $this->logger->info('Order Fetch Cron Disabled', ['path' => __METHOD__, 'Cron Status' => 'Disable']);
            }
            return false;
        } catch (\Exception $e){
            $this->logger->error('Order Import Cron', ['path' => __METHOD__, 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }
}
