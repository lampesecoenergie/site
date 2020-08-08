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

namespace Ced\Amazon\Block\Adminhtml\Account\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveAndContinue implements ButtonProviderInterface
{
    /**
     * @var \Magento\Backend\Block\Widget\Container
     */
    public $container;

    /**
     * SaveAndContinue constructor.
     * @param \Magento\Backend\Block\Widget\Container $container
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Container $container
    ) {
        $this->container = $container;
    }

    /**
     * Retrieve button-specified settings
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = false;
        /** @var boolean $isAjax, disabling button for profile request */
        $isAjax = $this->container->getRequest()->getParam('isAjax', false);
        if (empty($isAjax)) {
            $data = [
                'label' => __('Save and Continue Edit'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit'],
                    ],
                    'form-role' => 'save',
                ],
                'sort_order' => 90,
                'on_click' => '',
            ];
        }

        return $data;
    }
}