<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Block\Adminhtml\Sliders;

use \Magento\Framework\Registry;
use \Magento\Backend\Block\Widget\Context;
		
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
     * Initialize Sliders edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        
		$this->_blockGroup = 'Mageants_BannerSlider';
        
		$this->_controller = 'adminhtml_sliders';
		
        parent::_construct();
        
		$this->buttonList->update('save', 'label', __('Save Slider'));
        
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
		
        $this->buttonList->update('delete', 'slider', __('Delete Slider'));
    }
	
    /**
     * Retrieve text for header element depending on loaded Slider
     *
     * @return string
     */
    public function getHeaderText()
    {
        $slider = $this->_coreRegistry->registry('mageants_bannerslider');
        
		if ($slider->getId()) 
		{
            return __("Edit Slider '%1'", $this->escapeHtml($slider->getSliderName()));
        }
		
        return __('New Slider');
    }
}
