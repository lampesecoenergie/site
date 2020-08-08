<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Block\Adminhtml\Slides\Edit\Tab;

use \Magento\Cms\Model\Wysiwyg\Config;
use \Magento\Backend\Block\Template\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\Data\FormFactory;
use \Magento\Config\Model\Config\Source\Yesno;
use \Mageants\BannerSlider\Helper\Data;
		
class Setting extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Wysiwyg config
     * 
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

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
     * @param  Config $wysiwygConfig,
     * @param  Context $context,
     * @param  Registry $registry,
     * @param  FormFactory $formFactory,
	 * @param  Yesno $yesNo,
	 * @param  Data $helper,
     * @param array $data
     */
    public function __construct(
        Config $wysiwygConfig,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
		Yesno $yesNo,
		Data $helper,
        array $data = []
    )
    {
        $this->_wysiwygConfig            = $wysiwygConfig;
		
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
		/** @var \Mageants\BannerSlider\Model\Slides $slide */
		$slide = $this->_coreRegistry->registry('mageants_bannerslider_slides');
		
        $form = $this->_formFactory->create();
        
		$form->setHtmlIdPrefix('slidesetting_');
        
		$form->setFieldNameSuffix('slidesetting');
        
		 $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Slide Setting'),
                'class'  => 'fieldset-wide'
            ]
        );
		 
        $fieldset->addField(
            'orientation',
            'select',
            [
                'name'  => 'setting[orientation]',
                'label' => __('Speed'),
                'title' => __('Speed'),
                'required' => true,
				'values' => $this->_helper->getOriantationValues(),				
            ]
        );
		
       $fieldset->addField(
            'slice1-rotation',
            'text',
            [
                'name'  => 'setting[slice1-rotation]',
                'label' => __('Slice 1 Rotation'),
                'title' => __('Slice 1 Rotation'),
                'required' => true,
				'values' => $this->_yesNo->toOptionArray(),
				'note' => 'Accept nagative and positive value'
            ]
        ); 
		
       $fieldset->addField(
            'slice2-rotation',
            'text',
            [
                'name'  => 'setting[slice2-rotation]',
                'label' => __('Slice 2 Rotation'),
                'title' => __('Slice 2 Rotation'),
                'required' => true,
				'note' => 'Accept nagative and positive value'
            ]
        ); 
		
       $fieldset->addField(
            'slice1-scale',
            'text',
            [
                'name'  => 'setting[slice1-scale]',
                'label' => __('Slice 1 Scale'),
                'title' => __('Slice 1 Scale'),
                'required' => true,
				'note' => 'Accept only positive value'
            ]
        ); 
		
       $fieldset->addField(
            'slice2-scale',
            'text',
            [
                'name'  => 'setting[slice2-scale]',
                'label' => __('Slice 2 Scale'),
                'title' => __('Slice 2 Scale'),
                'required' => true,
				'note' => 'Accept only positive value'
            ]
        );
		
		$id = $this->getRequest()->getParam('id');
		
		if(!$id )
		{
			$slideData = $this->_helper->getDefaultSlideSetting();
			
			$slide->addData($slideData['setting']);
		}
		else
		{
			$settingData = $this->_helper->unserializeSetting($slide->getSlidesetting());
			
			$slide->addData($settingData['setting']);
		}
		
		$slideData = $this->_session->getData('mageants_bannerslider_slides_data', true);
        
		if ($slideData) 
		{
            $slide->addData($slideData);
        } 
        
		$form->addValues($slide->getData());  
        
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
