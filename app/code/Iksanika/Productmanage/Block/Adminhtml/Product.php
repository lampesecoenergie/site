<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog manage products block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Iksanika\Productmanage\Block\Adminhtml;

use \Magento\Backend\Block\Widget\Container;

class Product extends \Magento\Catalog\Block\Adminhtml\Product 
{

    /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'add_new_product',
            'label' => __('Add Product'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->_getAddProductButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Iksanika\Productmanage\Block\Adminhtml\Product\Grid', 'product.grid')
//            $this->getLayout()->createBlock('Magento\Catalog\Block\Adminhtml\Product\Grid', 'product.grid')
        );
        return \Magento\Backend\Block\Widget\Container::_prepareLayout();
    }

    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

}
