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
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Ui\DataProvider\Product\Grid\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class ProfileModifier implements ModifierInterface
{
    public $request;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param array $data
     * @return array
     * @since 100.1.0
     */
    public function modifyData(array $data)
    {
        /*$result = [];
        //$storeId = $this->request->getParam('store_id', 0);
        $profileId = $this->request->getParam('profile_id', null);
        if (!empty($profileId)) {
            foreach ($data as $item) {
                if (isset($item['amazon_profile_id']) && $item['amazon_profile_id'] == $profileId) {
                    $result[$item['entity_id']] = $item;
                }
            }
        } else {
            $result = $data;
        }*/

        return $data;
    }

    /**
     * @param array $meta
     * @return array
     * @since 100.1.0
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
