<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Cdiscount
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Cdiscount\Block\Adminhtml\Profile;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{


    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {

        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {

        $this->_objectId = 'profile_id';
        $this->_blockGroup = 'ced_cdiscount';
        $this->_controller = 'adminhtml_profile';

        parent::_construct();

        $this->updateButton('save', 'label', __('Save Profile'));
        $this->updateButton(
            'save',
            'onclick',
            'saveAndContinueEdit(\''.$this->getSaveUrl().'\',true)'
        );


        $this->addButton(
            'delete',
            [
                'label' => __('Delete'),
                'class' => 'delete',
                'onclick' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to do this?'
                    ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'area' => 'adminhtml'
            ],
            -1
        );

        $this->_formScripts[] = "
			function saveAndContinueEdit(urlTemplate,flag) {
			alert('true');
			    groupVendorPpcode_massaction = document.getElementById('groupVendorPpcode_massaction-form')
			    groupVendorPpcode_massaction.parentElement.removeChild(groupVendorPpcode_massaction);
			     new Insertion.Bottom('edit_form',			     
			     groupVendorPpcode_massactionJsObject.fieldTemplate(
			     {name: 'in_profile_products', 
			     value: groupVendorPpcode_massactionJsObject.checkedString}));
            if(flag) {
			        var editForm = jQuery('#edit_form');
                    editForm.attr('action', urlTemplate);
                    editForm.submit();
			    } 
			}
        ";

    }
    /**
     * {@inheritdoc}
     */
    public function addButton($buttonId, $data, $level = 0, $sortOrder = 0, $region = 'toolbar')
    {

        if ($this->getRequest()->getParam('popup')) {
            $region = 'header';
        }
        parent::addButton($buttonId, $data, $level, $sortOrder, $region);
    }



    public function getSaveAndContinueUrl($back)
    {

        return $this->getUrl('*/*/save', array(
            '_current'   => true,
            'back'       => $back,
            'active_tab' => null,
            'pcode' => $this->getRequest()->getParam('pcode',false),
            'section'=>'ced_csmarketplace',
            'website' => $this->getRequest()->getParam('website',false),
        ));

    }
    /**
     * Retrieve header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {

        if($this->_coreRegistry->registry('profile_data') && $this->_coreRegistry->registry('profile_data')->getId() ) {
            return __('Edit Profile "%s" ', $this->escapeHtml($this->_coreRegistry->registry('profile_data')->getName()));
        } else {
            return __('Add Profile');
        }
    }
    /**
     * Retrieve URL for validation
     *
     * @return string
     */
//    public function getValidationUrl()
//    {
//    	return $this->getUrl('*/*/validate', ['_current' => true]);
//    }

    /**
     * Retrieve URL for save
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl(
            '*/*/save',
            [
                'id' =>  $this->getRequest()->getParam('id'),
                'pcode' => $this->getRequest()->getParam('pcode',false),

            ]
        );
    }
    public function getDeleteUrl()
    {
        return $this->getUrl(
            '*/*/delete',
            ['back' => null, 'pcode' => $this->getRequest()->getParam('pcode')]
        );
    }

    /**
     * Retrieve URL for validation
     *
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', ['_current' => true]);
    }
}