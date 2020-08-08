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

namespace Ced\Cdiscount\Cron\Product;


class InventoryPrice
{
    public $logger;
    public $product;
    public $profile;
    public $config;
    public $productChange;

    /**
     * @param \Ced\Cdiscount\Helper\Logger $logger
     */
    public function __construct(
        \Ced\Cdiscount\Helper\Logger $logger,
        \Ced\Cdiscount\Helper\Product $product,
        \Ced\Cdiscount\Model\ResourceModel\Profile\Collection $profile,
        \Ced\Cdiscount\Helper\Config $config,
        \Ced\Cdiscount\Model\ResourceModel\ProductChange\CollectionFactory $collectionFactory
    ) {
        $this->logger = $logger;
        $this->product = $product;
        $this->profile = $profile;
        $this->config = $config;
        $this->productChange = $collectionFactory;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function execute()
    {
        $response = false;
        try {
            if ($this->config->getPriceCronStatus() == true) {
                $this->logger->info('Inventory Price cron run.');
                $productChange = $this->productChange->create()
                    ->addFieldToSelect('product_id');
                if ($productChange->getSize() > 0) {
                    $productIds = $productChange->getColumnValues('product_id');
                    $response = $this->product->updatePriceInventory(array_unique($productIds));
                    if ($response == true) {
                        $productChange->walk('delete');
                    }
                }
            }
        } catch (\Exception $exception) {
            if ($this->config->getDebugMode() == true) {
                $this->logger->error($exception->getMessage(),
                    ['path' => __METHOD__, 'trace' => $exception->getTraceAsString()]);
            }
        }
        return $response;
    }
}
