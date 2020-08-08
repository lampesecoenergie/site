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
 * @package     Ced_2.2.5
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Helper\Api;


class CoreData extends \Magento\Framework\App\Helper\AbstractHelper implements \Ced\Integrator\Api\CoreDataInterface
{
    /**
     * @var \Magento\Config\Model\ResourceModel\Config $config
     */
    public $config;

    /**
     * CoreData constructor.
     * @param \Magento\Config\Model\ResourceModel\Config $config
     */

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Config\Model\ResourceModel\Config $config
    )
    {
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * Save Server Token in Magento.
     *
     * This call save the server token in Magento Config.
     *
     * @return mixed
     */
    public function saveToken()
    {
        $jwtToken = $this->_getRequest()->getParam('jwt');
        if ($jwtToken) {
            $this->config->saveConfig(
                'ced_integrator/settings/api_token',
                 $jwtToken,
                'default',
                 0
            );
        }
    }
}