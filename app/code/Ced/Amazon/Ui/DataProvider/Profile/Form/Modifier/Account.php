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

namespace Ced\Amazon\Ui\DataProvider\Profile\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\App\RequestInterface;

class Account implements ModifierInterface
{

    /** @var \Magento\Framework\App\RequestInterface $request  */
    public $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

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
        $id = $this->request->getParam('id');
        if ($id) {
//            if (isset($meta['profile_mappings']['children']['account_id']['data']['config'])) {
                $meta['profile_mappings']['children']['profile_account']['children']['account_id']['data']['config']['disabled'] = true;
//            }

//            if (isset($meta['profile_mappings']['children']['marketplace']['data']['options'])) {
//                $meta['profile_mappings']['children']['marketplace']['data']['options'] = true;
//            }

//            if (isset($meta['profile_mappings']['children']['marketplace']['data']['config'])) {
                $meta['profile_mappings']['children']['marketplace']['data']['config']['disabled'] = true;
//            }
        }
        return $meta;
    }
}
