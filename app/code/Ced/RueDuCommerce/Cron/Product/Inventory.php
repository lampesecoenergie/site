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

namespace Ced\RueDuCommerce\Cron\Product;

class Inventory
{
    public $logger;
    public $profileProduct;
    public $product;
    public $systemLogger;

    /**
     * @param \Ced\RueDuCommerce\Helper\Logger $logger
     */
    public function __construct(
        \Ced\RueDuCommerce\Model\ProfileProduct $profileProduct,
        \Ced\RueDuCommerce\Helper\Logger $logger,
        \Ced\RueDuCommerce\Helper\Product $product,
        \Psr\Log\LoggerInterface $systemLogger,
        \Ced\RueDuCommerce\Helper\Config $config
    ) {
        $this->profileProduct = $profileProduct;
        $this->logger = $logger;
        $this->product = $product;
        $this->systemLogger = $systemLogger;
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function execute()
    {
        $invCron = $this->config->getInventoryPriceCron();
        if($invCron == '1') {
            $productIds = $this->profileProduct->getCollection()->getColumnValues('product_id');
            $response = $this->product->updatePriceInventory($productIds);
            $this->logger->info('Inventory Sync Cron Response', ['path' => __METHOD__, 'ProductIds' => implode(',', $productIds), 'SyncReponse' => var_export($response)]);
            return $response;
        } else {
            $this->logger->info('Inventory Sync Cron Disabled', ['path' => __METHOD__, 'Cron Status' => 'Disable']);
        }
        return false;
    }
}
