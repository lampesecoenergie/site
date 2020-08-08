<?php
/**
 * Copyright © 2015 Iksanika. All rights reserved.
 * See IKS-COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Iksanika\Productmanage\Plugin\Catalog;

// @TODO: findout how to change declaration to pluging system
class ProductEditButtonBack
{
    public function __construct(\Magento\Framework\App\Config $config)
    {
        $this->_scopeConfig = $config;
    }

    public function afterGetButtonData(\Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Back $subject, $result)
    {
        if($this->_scopeConfig->getValue('iksanika_productmanage/columns/redirectAdvancedProductManager'))
        {
            $result['on_click'] = sprintf("location.href = '%s';", $subject->getUrl('productmanage/product/index'));
        }
        return $result;
    }
}
