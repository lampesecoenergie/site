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

namespace Ced\EbayMultiAccount\Block\Adminhtml\Profile;

/**
 * Class Edit
 * @package Ced\EbayMultiAccount\Block\Adminhtml\Profile
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * Edit constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {

        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'profile_id';
        $this->_blockGroup = 'ced_ebayMultiAccount';
        $this->_controller = 'adminhtml_profile';

        parent::_construct();

        $this->updateButton('save', 'label', __('Save'));
        $this->updateButton(
            'save',
            'onclick',
            'saveAndContinueEdit(\'' . $this->getSaveUrl() . '\',false)'
        );

        $this->addButton(
            'delete',
            [
                'label' => __('Delete'),
                'class' => 'delete',
                'onclick' => 'deleteConfirm(\'' . __('Are you sure you want to delete this?') . '\', \'' . $this->getDeleteUrl() . '\')',
                'area' => 'adminhtml'
            ],
            -1
        );

        $this->addButton(
            'save_and_edit_button',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'onclick' => 'saveAndContinueEdit(\'' . $this->getSaveAndContinueUrl('edit') . '\',true)',
            ]
        );

        $this->_formScripts[] = "
            function saveAndContinueEdit(urlTemplate,flag) {
                groupVendorPpcode_massaction = document.getElementById('groupVendorPpcode_massaction-form');
                if(groupVendorPpcode_massaction != null) {
                    groupVendorPpcode_massaction.parentElement.removeChild(groupVendorPpcode_massaction);
                     new Insertion.Bottom('edit_form',               
                     groupVendorPpcode_massactionJsObject.fieldTemplate(
                     {name: 'in_profile_products', 
                     value: groupVendorPpcode_massactionJsObject.checkedString}));
                 }
            if(flag) {
                    var editForm = jQuery('#edit_form');
                    editForm.attr('action', urlTemplate);
                    editForm.submit();
                } 
            }
        ";
    }

    /**
     * @param string $buttonId
     * @param array $data
     * @param int $level
     * @param int $sortOrder
     * @param string $region
     */
    public function addButton($buttonId, $data, $level = 0, $sortOrder = 0, $region = 'toolbar')
    {
        if ($this->getRequest()->getParam('popup')) {
            $region = 'header';
        }
        parent::addButton($buttonId, $data, $level, $sortOrder, $region);
    }

    /**
     * @param $back
     * @return string
     */
    public function getSaveAndContinueUrl($back)
    {
        return $this->getUrl('*/*/save', [
            '_current' => true,
            'back' => $back,
            'active_tab' => null,
            'pcode' => $this->getRequest()->getParam('pcode', false),
            'website' => $this->getRequest()->getParam('website', false),
        ]);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {

        if ($this->_coreRegistry->registry('profile_data') && $this->_coreRegistry->registry('profile_data')->getId()) {
            return __('Edit Profile "%s" ', $this->escapeHtml($this->_coreRegistry->registry('profile_data')->getName()));
        } else {
            return __('Add Profile');
        }
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl(
            '*/*/save',
            ['_current' => true, 'back' => null, 'pcode' => $this->getRequest()->getParam('pcode')]
        );
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl(
            '*/*/delete',
            ['back' => null, 'pcode' => $this->getRequest()->getParam('pcode')]
        );
    }
}
