<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Block\Adminhtml\Slides;

use  \Magento\Framework\Registry;
use  \Magento\Backend\Block\Widget\Context;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     * 
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * constructor
     * 
     * @param Registry $coreRegistry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        Context $context,
        array $data = []
    )
    {
        $this->_coreRegistry = $coreRegistry;
		
        parent::__construct($context, $data);
    }

    
    /**
     * Initialize Slides edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
		
        $this->_blockGroup = 'Mageants_BannerSlider';
		
        $this->_controller = 'adminhtml_slides';
		
        parent::_construct();
		
        $this->buttonList->update('save', 'slides', __('Save Slide'));
		
		/* Add save and continue button */
        $this->buttonList->add(
            'save-and-continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
		
		$slide = $this->_coreRegistry->registry('mageants_bannerslider_slides');
		
		$sliderid = $this->getRequest()->getParam('sliderid');
		
		/*Modify delete button title*/
        $this->buttonList->update('delete', 'slides', __('Delete Slide'));
    }
	
    /**
     * Retrieve text for header element depending on loaded Slide
     *
     * @return string
     */
    public function getHeaderText()
    {
        $slide = $this->_coreRegistry->registry('mageants_bannerslider_slides');
		
        if ($slide->getId()) 
		{
            return __("Edit Slide '%1'", $this->escapeHtml($slide->getTitle()));
        }
		
        return __('New Slide');
    }
}
