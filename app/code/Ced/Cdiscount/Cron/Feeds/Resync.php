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

namespace Ced\Cdiscount\Cron\Feeds;

class Resync
{
    public $logger;
    public $product;
    public $profile;
    public $config;
    public $feedsCollectionFactory;

    /**
     * @param \Ced\Cdiscount\Helper\Logger $logger
     */
    public function __construct(
        \Ced\Cdiscount\Helper\Logger $logger,
        \Ced\Cdiscount\Helper\Product $product,
        \Ced\Cdiscount\Model\ResourceModel\Profile\Collection $profile,
        \Ced\Cdiscount\Helper\Config $config,
        \Ced\Cdiscount\Model\ResourceModel\Feeds\CollectionFactory $feedsCollectionFactory
    ) {
        $this->logger = $logger;
        $this->feedsCollectionFactory = $feedsCollectionFactory;
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
        $response = false;
//        try {
//            if ($this->config->getFeedsCronStatus() == true) {
//                $feedUniqueName = $this->feedsCollectionFactory->create()
//                    ->addFieldToFilter('feed_id', ['eq' => 'empty'])
//                    ->addFieldToSelect('unique_name')
//                    ->getFirstItem()->getUniqueName();
//                $this->product->sendProductPackage($feedUniqueName);
//            }
//        } catch (\Exception $exception) {
//            if ($this->config->getDebugMode() == true) {
//                $this->logger->error($exception->getMessage(),
//                    ['path' => __METHOD__, 'trace' => $exception->getTraceAsString()]);
//            }
//        }
        return $response;
    }
}
