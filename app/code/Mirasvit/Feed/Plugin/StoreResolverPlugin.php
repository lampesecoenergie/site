<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Plugin;

use Magento\Store\Model\StoreManagerInterface;

class StoreResolverPlugin
{
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    public function afterGetCurrentStoreId($subject, $storeId)
    {
        if (isset($_SERVER['FEED_STORE_ID']) && $storeId !== $_SERVER['FEED_STORE_ID']) {
            return $_SERVER['FEED_STORE_ID'];
        }

        return $storeId;
    }
}