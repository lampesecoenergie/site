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
 * @package     Ced_Amazon
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2019 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Ui\DataProvider\Product\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class Amazon implements ModifierInterface
{

    /**
     * @param array $data
     * @return array
     * @since 100.1.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     * @since 100.1.0
     */
    public function modifyMeta(array $meta)
    {
        if (isset($meta['amazon']['children']['container_amazon_profile_id']['children']['amazon_profile_id']['arguments']['data']['config'])) {
            $meta['amazon']['children']['container_amazon_profile_id']['children']['amazon_profile_id']['arguments']['data']['config']['disabled'] = true;
        }

        if (isset($meta['amazon']['children']['container_amazon_product_status']['children']['amazon_product_status']['arguments']['data']['config'])) {
            $meta['amazon']['children']['container_amazon_product_status']['children']['amazon_product_status']['arguments']['data']['config']['disabled'] = true;
        }

        if (isset($meta['amazon']['children']['container_amazon_validation_errors']['children']['amazon_validation_errors']['arguments']['data']['config'])) {
            $meta['amazon']['children']['container_amazon_validation_errors']['children']['amazon_validation_errors']['arguments']['data']['config']['disabled'] = true;
        }

        if (isset($meta['amazon']['children']['container_amazon_feed_errors']['children']['amazon_feed_errors']['arguments']['data']['config'])) {
            $meta['amazon']['children']['container_amazon_feed_errors']['children']['amazon_feed_errors']['arguments']['data']['config']['disabled'] = true;
        }

        return $meta;
    }
}
