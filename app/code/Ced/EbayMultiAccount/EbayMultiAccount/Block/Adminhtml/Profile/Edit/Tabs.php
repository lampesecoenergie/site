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

namespace Ced\EbayMultiAccount\Block\Adminhtml\Profile\Edit;

/**
 * Class Tabs
 * @package Ced\EbayMultiAccount\Block\Adminhtml\Profile\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('profile_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Profile Information'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'info',
            [
                'label' => __('Profile info'),
                'title' => __('Profile Info'),
                'content' => $this->getLayout()->createBlock('Ced\EbayMultiAccount\Block\Adminhtml\Profile\Edit\Tab\Info')->toHtml(),
                'active' => true
            ]
        );

        $this->addTab(
            'mapping',
            [
                'label' => __('Category & Attribute'),
                'title' => __('Category & Attribute'),
                'content' => $this->getLayout()->createBlock('Ced\EbayMultiAccount\Block\Adminhtml\Profile\Edit\Tab\Mapping', 'mapping')->toHtml(),
            ]
        );

        $this->addTab(
            'profile_products',
            [
                'label' => __('Profile Products'),
                'title' => __('Profile Products'),
                'content' => $this->getLayout()->createBlock('Ced\EbayMultiAccount\Block\Adminhtml\Profile\Edit\Tab\Products', 'profile_products')->toHtml(),
            ]
        );

        return parent::_beforeToHtml();
    }

    /**
     * @return string
     */
    public function getAttributeTabBlock()
    {
        return 'Ced\EbayMultiAccount\Block\Adminhtml\Profile\Edit\Tab\Info';
    }
}
