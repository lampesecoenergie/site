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

namespace Ced\Amazon\Block\Adminhtml\Product;

use Magento\Backend\Block\Widget\Container;

/**
 * @deprecated: Marked for Removal
 * Class Button
 * @package Ced\Amazon\Block\Adminhtml\Product
 */
class Button extends Container
{

    /**
     * Button constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        /*$addButtonProps = [
            'id' => 'select_profile',
            'label' => __('Select Profile '),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->_getAddProductButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);*/

        return parent::_prepareLayout();
    }

    /**
     * Retrieve options for 'Add Product' split button
     *
     * @return array
     */
    public function _getAddProductButtonOptions()
    {
        $splitButtonOptions = [];

        $splitButtonOptions['massimport'] = [
            'label' => __('Bulk Product Upload'),
            'onclick' => "setLocation('" . $this->getUrl(
                'amazon/product/additems'
            ) . "')",
            'default' => true,
        ];

        $splitButtonOptions['sync_price_inv'] = [
            'label' => __('Sync Inventory And Price'),
            'onclick' => "setLocation('" . $this->getUrl(
                'amazon/product/sync'
            ) . "')",
            'default' => false,
        ];
        return $splitButtonOptions;
    }
}
