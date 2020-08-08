<?php
/**
 * Copyright © 2015 Iksanika. All rights reserved.
 * See IKS-COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Iksanika\Productmanage\Plugin\Catalog;

// @TODO: findout how to change declaration to pluging system
class ProductEditActionAttribute extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute
{

    public function afterSetLayout(\Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute $subject, $result)
    {
        if(!$this->_scopeConfig->getValue('iksanika_productmanage/columns/redirectAdvancedProductManager'))
            $redirectPrefix = 'catalog/product/';
        else
            $redirectPrefix = 'productmanage/product/index';

        $onClick = 'setLocation(\'' . $this->getUrl(
                    $redirectPrefix,
                    ['store' => $this->getRequest()->getParam('store', 0)]
                ) . '\')';

        $result->getToolbar()->getChildBlock('back_button')->setData('onclick', $onClick);
        return $result;
    }


}
