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


class AllInventoryPrice
{
    public $logger;
    public $product;
    public $profile;
    public $config;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Ced\Cdiscount\Helper\Logger $logger,
        \Ced\Cdiscount\Helper\Product $product,
        \Ced\Cdiscount\Model\ResourceModel\Profile\Collection $profile,
        \Ced\Cdiscount\Helper\Config $config
    ) {
        $this->products = $collectionFactory;
        $this->logger = $logger;
        $this->product = $product;
        $this->profile = $profile;
        $this->config = $config;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function execute()
    {
        try {
            if ($this->config->getAllInvPriceStatus() == true) {
                $this->logger->info('All InventoryPrice cron run.');
                $activatedProfileIds = $this->profile->addFieldToFilter('profile_status',['eq' => 1])
                    ->getAllIds();
                $productIds = $this->products->create()->addAttributeToSelect(
                    [
                        'entity_id',
                        'cdiscount_profile_id'
                    ]
                )
                    ->addAttributeToFilter('cdiscount_profile_id', ['in' => $activatedProfileIds])
                    ->getAllIds();
                $response = $this->product->updatePriceInventory($productIds);
                return $response;
            }
            return false;
        } catch (\Exception $exception) {
            if ($this->config->getStore() == true) {
                $this->logger->error($exception->getMessage(), ['path' => __METHOD__]);
            }
        }
        return false;
    }
}
