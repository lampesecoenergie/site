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

namespace Ced\Amazon\Block\Adminhtml\Order\Button;

/**
 * Class Import
 * @package Ced\Amazon\Block\Adminhtml\Order\Button
 */
class Import extends \Magento\Backend\Block\Widget\Container implements
    \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    public function getButtonData()
    {
        $button = [
            'id' => 'order_import_button',
            'label' => __('Import'),
            'class' => 'add',
            'class_name' => \Magento\Backend\Block\Widget\Button\SplitButton::class,
            'options' => $this->buttonOptions(),
        ];
        return $button;
    }

    /**
     * Retrieve options for 'Import Product' split button
     *
     * @return array
     */
    public function buttonOptions()
    {
        $splitButtonOptions = [];

        $splitButtonOptions['order_import_pending'] = [
            'label' => __('Import Unshipped'),
            'onclick' => "setLocation('" . $this->getUrl('amazon/order/import') . "')",
            'default' => false,
        ];

        $splitButtonOptions['order_search_import'] = [
            'label' => __('Search and Import'),
            'onclick' => "setLocation('" . $this->getUrl('amazon/order/searchimport') . "')",
            'default' => true,
        ];
        return $splitButtonOptions;
    }
}
