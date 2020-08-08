<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Block\Adminhtml\Sliders\Edit\Tab;

use \Magento\Backend\Block\Template\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\Data\FormFactory;
use \Magento\Config\Model\Config\Source\Yesno;
use \Mageants\BannerSlider\Helper\Data;

class Setting extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
	/**
     * Yes No options
     * 
     */
    protected $_yesNo;
    
	/**
     * Default Helper options
     * 
     */
    protected $_helper;
	
    /**
     * constructor
     * 
     * @param  Context $context,
     * @param  Registry $registry,
     * @param  FormFactory $formFactory,
	 * @param  Yesno $yesNo,
	 * @param  Data $helper,
     * @param  array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
		Yesno $yesNo,
		Data $helper,
        array $data = []
    )
    {
        $this->_yesNo 					 = $yesNo;
		
		$this->_helper 					 = $helper;
		
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Mageants\BannerSlider\Model\Sliders $slider */
        $slider = $this->_coreRegistry->registry('mageants_bannerslider');
		
        $form = $this->_formFactory->create();
		
        $form->setHtmlIdPrefix('slidersetting_');
        $form->setFieldNameSuffix('slidersetting');
        
		 $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Slider Setting'),
                'class'  => 'fieldset-wide'
            ]
        );
		 
        $fieldset->addField(
            'width',
            'text',
            [
                'name'  => 'setting[width]',
                'label' => __('Slider Width'),
                'title' => __('Slider Width'),                
				'note' => " Slider width in pixel or leave blank if you want 100% width"
            ]
        );
		
		 
        $fieldset->addField(
            'height',
            'text',
            [
                'name'  => 'setting[height]',
                'label' => __('Slider Height'),
                'title' => __('Slider Height'),
				'note' => " Slider height in pixel or leave blank if you want auto height"
            ]
        );
		
		 
        $fieldset->addField(
            'speed',
            'text',
            [
                'name'  => 'setting[speed]',
                'label' => __('Speed'),
                'title' => __('Speed'),
                'required' => true,
				'note' => " transitions speed"
            ]
        );
		
       $fieldset->addField(
            'autoplay',
            'select',
            [
                'name'  => 'setting[autoplay]',
                'label' => __('Autoplay'),
                'title' => __('Autoplay'),
                'required' => true,
				'values' => $this->_yesNo->toOptionArray(),
				'note' => 'slideshow on / off'
            ]
        ); 
		
       $fieldset->addField(
            'keyboard',
            'select',
            [
                'name'  => 'setting[keyboard]',
                'label' => __('Keyboard Navigation'),
                'title' => __('Keyboard Navigation'),
                'required' => true,
				'values' => $this->_yesNo->toOptionArray(),
				'note' => 'Enable / Disable keyboard navigation'
            ]
        ); 
		
       $fieldset->addField(
            'show_navigation_arrows',
            'select',
            [
                'name'  => 'setting[show_navigation_arrows]',
                'label' => __('Show Navigation Arrows'),
                'title' => __('Show Navigation Arrows'),
                'required' => true,
				'values' => $this->_yesNo->toOptionArray(),
				'note' => 'Show / Hide Arrow navigation'
            ]
        ); 
		
       $fieldset->addField(
            'show_navigation_bullets',
            'select',
            [
                'name'  => 'setting[show_navigation_bullets]',
                'label' => __('Show Navigation Bullets'),
                'title' => __('Show Navigation Bullets'),
                'required' => true,
				'values' => $this->_yesNo->toOptionArray(),
				'note' => 'Show / Hide Bullets navigation'
            ]
        ); 
		
       $fieldset->addField(
            'interval',
            'text',
            [
                'name'  => 'setting[interval]',
                'label' => __('Interval'),
                'title' => __('Interval'),
                'required' => true,
				'note' => 'time between transitions'
            ]
        ); 
		
		
		$sliderDefaultConfig = $this->_helper->getDefaultSliderSetting();
		
		$slider->addData($sliderDefaultConfig['setting']);		
		
		$sliderData = $this->_session->getData('mageants_bannerslider_slider_data', true);
        
		if ($sliderData) 
		{
            $slider->addData($sliderData);
        } 
		else 
		{
            if (!$slider->getId()) 
			{		
                $slider->addData($slider->getDefaultValues());
            }
			else
			{
				$settingData = $this->_helper->unserializeSetting($slider->getSetting());
				
				$slider->addData($settingData['setting']);
			}
        } 
		
        $form->addValues($slider->getData()); 
		
        $this->setForm($form);
		
        return parent::_prepareForm();
    }

    /**
     * Prepare Slider for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Setting');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
