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
 * @package   Ced_m2.2.EE
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Cdiscount\Observer;


use Magento\Framework\Event\Observer;

class ProductSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    public $logger;
    public $config;
    public $productChangeFactory;
    public $stockState;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Ced\Cdiscount\Helper\Logger $logger,
        \Ced\Cdiscount\Helper\Config $config,
        \Ced\Cdiscount\Model\ProductChangeFactory $changeFactory
    )
    {
        $this->config = $config;
        $this->productChangeFactory = $changeFactory;
        $this->stockState = $stockState;
        $this->logger = $logger;
    }

    /*
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $response = true;
        $productId = 0;
        try {
            $product = $observer->getEvent()->getProduct();
            if (empty($product) && !method_exists($product, 'getId')) {
                $response =  false;
            }

            $productId = $product->getId();
            $productChange = $this->productChangeFactory->create();
            $productChange->load($productId,'product_id');
            $productChange->setData('product_id', $productId);
            $productChange->setData('type', \Ced\Cdiscount\Model\ProductChange::PRODUCT_CHANGE_TYPE_INVENTORY_PRICE);
            $productChange->setData('action', \Ced\Cdiscount\Model\ProductChange::PRODUCT_CHANGE_ACTION_UPDATE);
            $productChange->save();
            $response =  true;
        } catch (\Exception $exception) {
            if ($this->config->getDebugMode() == true) {
                $this->logger->error($exception->getMessage(),
                    ['path' => __METHOD__, 'product_id' => $productId]);
            }
        }
        return $response;
    }
}