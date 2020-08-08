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

namespace Ced\EbayMultiAccount\Block\Adminhtml\AccountConfig\Edit;

/**
 * Class Tabs
 * @package Ced\EbayMultiAccount\Block\Adminhtml\AccountConfig\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('accountconfig_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Configuration Setting'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'info',
            [
                'label' => __('Configuration Details'),
                'title' => __('Configuration Details'),
                'content' => $this->getLayout()->createBlock('Ced\EbayMultiAccount\Block\Adminhtml\AccountConfig\Edit\Tab\Info')->toHtml(),
                'active' => true
            ]
        );

        $this->addTab(
            'accountconfig',
            [
                'label' => __('Required Configuration'),
                'title' => __('Payment, Shippment and Return Policy'),
                'content' => $this->getLayout()->createBlock('Ced\EbayMultiAccount\Block\Adminhtml\AccountConfig\Edit\Tab\Configuration', 'accountconfig')->toHtml(),
            ]
        );
        return parent::_beforeToHtml();
    }

    /**
     * @return string
     */
    public function getAttributeTabBlock()
    {
        return 'Ced\EbayMultiAccount\Block\Adminhtml\AccountConfig\Edit\Tab\Info';
    }
}
