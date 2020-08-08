<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer edit block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Iksanika\Productmanage\Block\Adminhtml\Product;

class Edit extends \Magento\Catalog\Block\Adminhtml\Product\Edit
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Catalog::catalog/product/edit.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $_attributeSetFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_productHelper;

    /**
     * Add elements in layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if(!$this->_scopeConfig->getValue('iksanika_productmanage/columns/redirectAdvancedProductManager'))
            $redirectPrefix = 'catalog/*/';
        else
            $redirectPrefix = 'productmanage/*/';
        
        if (!$this->getRequest()->getParam('popup')) {
            if ($this->getToolbar()) {
                $this->getToolbar()->addChild(
                    'back_button',
                    'Magento\Backend\Block\Widget\Button',
                    [
                        'label' => __('Back'),
                        'title' => __('Back'),
                        'onclick' => 'setLocation(\'' . $this->getUrl(
//                            'catalog/*/',
                            $redirectPrefix,
                            ['store' => $this->getRequest()->getParam('store', 0)]
                        ) . '\')',
                        'class' => 'action-back'
                    ]
                );
            }
        } else {
            $this->addChild(
                'back_button',
                'Magento\Backend\Block\Widget\Button',
                ['label' => __('Close Window'), 'onclick' => 'window.close()', 'class' => 'cancel']
            );
        }

        if (!$this->getProduct()->isReadonly()) {
            $this->addChild(
                'reset_button',
                'Magento\Backend\Block\Widget\Button',
                [
                    'label' => __('Reset'),
                    'onclick' => 'setLocation(\'' . $this->getUrl('catalog/*/*', ['_current' => true]) . '\')'
                ]
            );
        }

        if (!$this->getProduct()->isReadonly() && $this->getToolbar()) {
            $this->getToolbar()->addChild(
                'save-split-button',
                'Magento\Backend\Block\Widget\Button\SplitButton',
                [
                    'id' => 'save-split-button',
                    'label' => __('Save'),
                    'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
                    'button_class' => 'widget-button-save',
                    'options' => $this->_getSaveSplitButtonOptions()
                ]
            );
        }

        return \Magento\Backend\Block\Widget::_prepareLayout();
    }
}
