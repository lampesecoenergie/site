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
 * @package   Ced_Amazon
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Amazon\Block\Adminhtml\Report\Button;

/**
 * Class Request
 * @package Ced\Amazon\Block\Adminhtml\Report\Button
 */
class Request extends \Magento\Backend\Block\Widget\Container implements
    \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    public function getButtonData()
    {
        $button = [
            'id' => 'order_import_button',
            'label' => __('Request'),
            'class' => 'add',
            'class_name' => \Magento\Backend\Block\Widget\Button\SplitButton::class,
            'options' => $this->buttonOptions(),
        ];
        return $button;
    }

    public function buttonOptions()
    {
        $splitButtonOptions = [];

        $splitButtonOptions['report_product'] = [
            'label' => __('Product'),
            'onclick' => "setLocation('" . $this->getUrl('amazon/report/generate') . "')",
            'default' => false,
        ];

        $splitButtonOptions['report_request'] = [
            'label' => __('Select'),
            'onclick' => "setLocation('" . $this->getUrl('amazon/report/request_form') . "')",
            'default' => true,
        ];

        return $splitButtonOptions;
    }
}
