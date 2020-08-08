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

class Advsetting extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
                'legend' => __('Slider Advance Setting'),
                'class'  => 'fieldset-wide'
            ]
        );
		 
        $fieldset->addField(
            'optopacity',
            'select',
            [
                'name'  => 'setting[optopacity]',
                'label' => __('Opacity'),
                'title' => __('Opacity'),
                'required' => true,
				'values' => $this->_yesNo->toOptionArray(),
				'note' => " if true the item's slices will also animate the opacity value"
            ]
        );
		
       $fieldset->addField(
            'translatefactor',
            'text',
            [
                'name'  => 'setting[translatefactor]',
                'label' => __('Translate Factor'),
                'title' => __('Translate Factor'),
                'required' => true,
                'note' => "amount (%) to translate both slices - adjust as necessary"
            ]
        ); 
		
       $fieldset->addField(
            'maxangle',
            'text',
            [
                'name'  => 'setting[maxangle]',
                'label' => __('Max Angle'),
                'title' => __('Max Angle'),
                'required' => true,
				'note' => "maximum possible angle"
            ]
        ); 
		
       $fieldset->addField(
            'maxscale',
            'text',
            [
                'name'  => 'setting[maxscale]',
                'label' => __('Max Scale'),
                'title' => __('Max Scale'),
                'required' => true,
				'note' => "maximum possible scale"
            ]
        ); 
		
       $fieldset->addField(
            'onbeforechange',
            'textarea',
            [
                'name'  => 'setting[onbeforechange]',
                'label' => __('Default JavaScript for A slide Change'),
                'title' => __('Default JavaScript for Before slide Change'),
               'note' => "function( slide, idx ) { // Your Script call here	}"
            ]
        ); 
		
       $fieldset->addField(
            'onafterchange',
            'textarea',
            [
                'name'  => 'setting[onafterchange]',
                'label' => __('Default JavaScript for After slide Change'),
                'title' => __('Default JavaScript for After slide Change'),
               'note' => "function( slide, idx ) { // Your Script call here	}"
            ]
        ); 
		
		
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
        return __('Advance Setting');
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
