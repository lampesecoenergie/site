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
 * @package     Ced_Integrator
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright © 2018 CedCommerce. All rights reserved.
 * @license     EULA http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Integrator\Block\Adminhtml\Setup\Ui\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Register extends \Magento\Backend\Block\Widget\Container implements ButtonProviderInterface
{
    public $config;
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
       \Ced\Integrator\Helper\Config $config,
        array $data = []
    )
    {
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve button-specified settings
     *
     * @return array
     */
    public function getButtonData()
    {
        if ($this->config->isAlreadyRegistered()) {
            return [
                'label' => __('Login'),
                'on_click' => '',
                'class' => 'save primary',
                'data_attribute' => [
                    'form-role' => 'save',
                ],
                'sort_order' => 10
            ];
        } else {
            return [
                'label' => __('Register'),
                'on_click' => '',
                'class' => 'save primary',
                'data_attribute' => [
                    'form-role' => 'save',
                ],
                'sort_order' => 10
            ];
        }
    }

}
