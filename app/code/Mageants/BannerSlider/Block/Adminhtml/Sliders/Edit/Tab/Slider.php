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
use \Magento\Cms\Ui\Component\Listing\Column\Cms\Options;
use \Magento\Config\Model\Config\Source\Yesno;
use \Mageants\BannerSlider\Model\Source\Status;

class Slider extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Store View options
     * 
     */
    protected $_cmsOpt;
	
    /**
     * Yes No options
     * 
     */
    protected $_yesNo;

    /**
     * Enable / Disable options
     * 
     */
    protected $_status;

    /**
     * constructor
     * 
     * @param Context $context
	 * @param Options $cmsOpt
	 * @param Yesno $yesNo
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
		Options $cmsOpt,
		Yesno $yesNo,
		Status $status,
        array $data = []
    )
    {	
        $this->_cmsOpt 				 = $cmsOpt;
		
        $this->_yesNo 					 = $yesNo;
        
		$this->_status 					 = $status;
		
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
		
        $form->setHtmlIdPrefix('slider_');
        $form->setFieldNameSuffix('slider');
        
		 $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Slider Information'),
                'class'  => 'fieldset-wide'
            ]
        );
		 
      if ($slider->getId()) 
	  {
            $fieldset->addField(
                'id',
                'hidden',
                ['name' => 'id']
            );
        }
		
        $fieldset->addField(
            'status',
            'select',
            [
                'name'  => 'status',
                'label' => __('Enable'),
                'title' => __('Enable'),
                'required' => true,
				'values' => $this->_status->toOptionArray()
            ]
        );
		
        $fieldset->addField(
            'slider_name',
            'text',
            [
                'name'  => 'slider_name',
                'label' => __('Slider Name'),
                'title' => __('Slider Name'),
                'required' => true,
            ]
        );
		
        $fieldset->addField(
            'store_id',
            'select',
            [
                'name'  => 'store_id',
                'label' => __('Store View'),
                'title' => __('Store View'),
                'required' => true,
				'values' => $this->_cmsOpt->toOptionArray()
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
        return __('General');
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
