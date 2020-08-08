<?php
/**
 * Copyright © 2015 Iksanika. All rights reserved.
 * See IKS-LICENSE.txt for license details.
 */
namespace Iksanika\Productmanage\Controller\Adminhtml\Product;

class Grid extends \Magento\Catalog\Controller\Adminhtml\Product\Grid 
{
    /**
     * Product grid for AJAX request
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }

    /**
     * Determine if authorized to perform group actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Iksanika_Productmanage::productmanage');
    }
}
