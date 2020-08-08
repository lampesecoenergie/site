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
 * @package   Ced_EbayMultiAccount
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\EbayMultiAccount\Block\Adminhtml\Product;

/**
 * Class Button
 * @package Ced\EbayMultiAccount\Block\Adminhtml\Product
 */
class Button extends \Magento\Backend\Block\Widget\Container
{
    /**
     * Button constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'ebaymultiaccount_product_button',
            'label' => __('Bulk Actions'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->_getAddProductButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        return parent::_prepareLayout();
    }

    /**
     * @return array
     */
    public function _getAddProductButtonOptions()
    {
        $splitButtonOptions = [];

        $splitButtonOptions['massimport'] = [
            'label' => __('Product Status Sync'),
            'onclick' => "setLocation('" . $this->getUrl(
                    'ebaymultiaccount/product/bulkstatussync') . "')",
            'default' => true,
        ];

        /*$splitButtonOptions['sync_price_inv'] = [
                'label' => __('Sync Inventory And Price'),
                'onclick' => "setLocation('" . $this->getUrl(
                  'ebaymultiaccount/product/bulkinvpricesync') . "')",
                'default' => false,
            ];*/

        return $splitButtonOptions;
    }

}
