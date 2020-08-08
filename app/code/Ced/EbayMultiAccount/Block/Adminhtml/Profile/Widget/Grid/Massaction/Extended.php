<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ced\EbayMultiAccount\Block\Adminhtml\Profile\Widget\Grid\Massaction;

/**
 * Class Extended
 * @package Ced\EbayMultiAccount\Block\Adminhtml\Profile\Widget\Grid\Massaction
 */
class Extended extends \Magento\Backend\Block\Widget\Grid\Massaction\Extended
{

    /**
     * @var \Ced\EbayMultiAccount\Helper\MultiAccount
     */
    protected $multiAccountHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var
     */
    protected $_objectManager;
    /**
     * @var string
     */
    protected $_template = 'Ced_EbayMultiAccount::widget/grid/massaction.phtml';

    /**
     * @return string
     */
    public function getSelectedJson()
    {
        return join(",", $this->_getProducts());
    }

    /**
     * @param bool $isJson
     * @return array|string
     */
    public function _getProducts($isJson=false)
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->multiAccountHelper = $this->_objectManager->create('Ced\EbayMultiAccount\Helper\MultiAccount');

        if ($this->getRequest()->getPost('in_profile_products') != "") {
            return explode(",", $this->getRequest()->getParam('in_profile_products'));
        }

        $profileCode = $this->getRequest()->getParam('pcode');
        $profile = $this->_objectManager->get('Magento\Framework\Registry')->registry('current_profile');
        $currentAccount = $this->_objectManager->get('Magento\Framework\Registry')->registry('ebay_account');
        $profileAccountAttr = $this->multiAccountHelper->getProfileAttrForAcc($currentAccount->getId());

        if ($profile && $profile->getId()) {
            $profileId = $profile->getId();
        } else {
            $profileId = $this->_objectManager->create('Ced\EbayMultiAccount\Model\Profile')->getCollection()->addFieldToFilter('profile_code', $profileCode)->getColumnValues('id');
        }
        $productIds = [];
        if (!empty($profileId)) {
            $productIds  = $this->_objectManager->get('Magento\Catalog\Model\Product')->getCollection()->addAttributeToFilter($profileAccountAttr, $profileId)->getColumnValues('entity_id');
        }
        if (sizeof($productIds) > 0) {
            $products = $this->_objectManager->create('\Magento\Catalog\Model\Product')
                ->getCollection()
                ->addAttributeToFilter('visibility', array('neq' => 1))
                ->addAttributeToFilter('type_id', array('simple', 'configurable'))
                ->addFieldToFilter('entity_id', array('in' => $productIds));
            if ($isJson) {
                $jsonProducts = array();
                foreach($products as $product)  {
                    $jsonProducts[$product->getEntityId()] = 0;
                }
                return $this->_jsonEncoder->encode((object)$jsonProducts);
            } else {
                $jsonProducts = array();
                foreach($products as $product)  {
                    $jsonProducts[$product->getEntityId()] = $product->getEntityId();
                }
                return $jsonProducts;
            }
        } else {
            if ($isJson) {
                return '{}';
            } else {
                return array();
            }
        }
    }

    /**
     * @return string
     */
    public function getGridIdsJson()
    {
        if (!$this->getUseSelectAll()) {
            return '';
        }

        /** @var \Magento\Framework\Data\Collection $allIdsCollection */
        $allIdsCollection = clone $this->getParentBlock()->getCollection();
        $gridIds = $allIdsCollection->clear()->setPageSize(0)->getAllIds();

        if (!empty($gridIds)) {
            return join(",", $gridIds);
        }
        return '';
    }
}
