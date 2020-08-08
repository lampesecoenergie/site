<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Megamenu
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Megamenu\Block\Adminhtml\Menu;

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
     * @param \Magento\Framework\Registry           $registry 
     * @param array                                 $data     
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
     * Initialize cms page edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'menu_id';
        $this->_blockGroup = 'Ves_Megamenu';
        $this->_controller = 'adminhtml_menu';

        parent::_construct();

        if ($this->_isAllowedAction('Ves_Megamenu::menu_save')) {
            $this->buttonList->update('save', 'label', __('Save Menu'));
            if($this->_coreRegistry->registry('megamenu_menu')->getId()){
                $this->buttonList->add(
                    'duplicate',
                    [
                    'label' => __('Save and Duplicate'),
                    'class' => 'save'
                    ],
                    -50
                    );
            }
            $this->buttonList->add(
                'saveandcontinue',
                [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                'mage-init' => [
                'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                ],
                ]
                ],
                -100
                );
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('Ves_Megamenu::menu_delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Menu'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('megamenu_menu')->getId()) {
            return __("Edit Menu '%1'", $this->escapeHtml($this->_coreRegistry->registry('megamenu_menu')->getTitle()));
        } else {
            return __('New Menu');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            require([
                'jquery',
                'mage/backend/form'
                ], function(){
                jQuery('#duplicate').click(function(){
                    var actionUrl = jQuery('#edit_form').attr('action') + 'duplicate/1';
                    jQuery('#edit_form').attr('action', actionUrl);
                    jQuery('#edit_form').submit();
                });

                function toggleEditor() {
                    if (tinyMCE.getInstanceById('before_form_content') == null) {
                        tinyMCE.execCommand('mceAddControl', false, 'before_form_content');
                    } else {
                        tinyMCE.execCommand('mceRemoveControl', false, 'before_form_content');
                    }
                };
            });

";
return parent::_prepareLayout();
}
}