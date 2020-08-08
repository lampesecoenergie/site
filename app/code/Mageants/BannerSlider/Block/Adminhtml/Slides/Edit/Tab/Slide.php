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
use \Mageants\BannerSlider\Model\Source\Status;
use \Mageants\BannerSlider\Model\Source\SlideType;
use \Mageants\BannerSlider\Model\Sliders;
use \Mageants\BannerSlider\Block\Adminhtml\Slides\Edit\Tab\ProductGrid;

class Slide extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
	const FORM_NAME = 'mageants_banner_slide_form';
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
     *  Disable / Enable options
     * 
     */
    protected $_status;
    
	/**
     * Default Helper options
     */
    protected $_helper;
	
	/**
     * Default Helper options
     * 
     */
    protected $_slidersFactory;
	
    protected $slidedata;
	
    /**
     * constructor
     * 
     * @param Config $wysiwygConfig
     * @param Yesno $yesNo     
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        Config $wysiwygConfig,		
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
		Yesno $yesNo,
		Data $helper,
		Status $status,
		Sliders $sliderFactory,
		SlideType $slideTypeFactory,
        array $data = []
    )
    {
        $this->_wysiwygConfig        = $wysiwygConfig;
		
		$this->_yesNo 					 = $yesNo;
		
		$this->_helper 					 = $helper;
		
		$this->_status 					 = $status;
		
		$this->_slideTypeFactory 		 = $slideTypeFactory;
		
		$this->_slidersFactory 		 = $sliderFactory;
		
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Mageants\BannerSlider\Model\Slides $slider */
        $slide = $this->_coreRegistry->registry('mageants_bannerslider_slides');
        $slidedata = $slide->getData();
       
		$form = $this->_formFactory->create();
		
        $form->setHtmlIdPrefix('slide_');
        $form->setFieldNameSuffix('slide');
        
		 $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Slide Setting'),
                'class'  => 'fieldset-wide'
            ]
        );
		
		$fieldset->addType('image', 'Mageants\BannerSlider\Block\Adminhtml\Slides\Helper\Image');
		$fieldset->addType('file', 'Mageants\BannerSlider\Block\Adminhtml\Slides\Helper\File');
		
		
		$id = $this->getRequest()->getParam('id');
		
		if ($id) 
		{
            $fieldset->addField(
                'id',
                'hidden',
                ['name' => 'id']
            );
        }
		else{
			
			$slideData['show_cat_slide_if_no_image_found'] = '';
			
			$slideData['show_prod_slide_if_no_image_found'] = '';
			
			$slideData['category_ids'] = '';
			
			$slideData['product_ids'] = '';
			
			$slide->addData($slideData);
		}
		
		$fieldset->addField(
            'status',
            'select',
            [
                'name'  => 'status',
                'label' => __('Enable'),
                'title' => __('Enable'),
                'required' => true,
				'values' => $this->_status->getOptionArray()
            ]
        );
		
        $fieldset->addField(
            'title',
            'text',
            [
                'name'  => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true
            ]
        );
		
		$fieldset->addField(
			'slide_type',
			'select',
			[
				'name' => 'slide_type',
				'values' => $this->_slideTypeFactory->toOptionArray(),
				'label' => __('Slide Type'),
                'title' => __('Slide Type'),
				'required' => true,
			]
		);
		
		$fieldset->addField(
			'slider_id',
			'select',
			[
				'name' => 'slider_id',
				'values' => $this->_slidersFactory->toOptionArray(),
				'label' => __('Banner Slider'),
                'title' => __('Banner Slider'),
				'required' => true,
			]
		);
		
		$slideData = $this->_session->getData('mageants_bannerslider_slides_data', true);
        
		
		$sliderid = $this->getRequest()->getParam('sliderid');
		
		if ($sliderid) 
		{
            $slideData['slider_id'] = $sliderid;			
		}
		
		if ($slideData) 
		{
            $slide->addData($slideData);
        } 
		
		$form->addValues($slide->getData());  

		$formData = $slide->getData();
		
		$form = $this->addImageFieldset($form, $formData);
		
		$form = $this->addCategoryFieldset($form, $formData);
		
		$form = $this->addProductFieldset($form, $formData);
		
		
        $this->setForm($form);
		
		
        return parent::_prepareForm();
    }
	
    /**
     * Add Image fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @param array $formData
     * @return \Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addImageFieldset($form, $formData)
	{
            if(isset($formData['image'])){
                $fieldset = $form->addFieldset( 'image_fieldset', []  );
                $fieldset->addField(
                    'image',
                    'image',
                    [
                        'name'  => 'image',
                        'label' => __('Banner Image'),
                        'title' => __('Banner Image'),
                        'value' => $formData['image'],
                    ]
                );
                
                $fieldset->addField(
                    'content',
                    'editor',
                    [
                        'name'  => 'content',
                        'label' => __('Content'),
                        'title' => __('Content'),
                        'value' => $formData['content'],
                        'config'    => $this->_wysiwygConfig->getConfig()
                    ]
                );
            }else{
                $fieldset = $form->addFieldset( 'image_fieldset', []  );
                $fieldset->addField(
                    'image',
                    'image',
                    [
                        'name'  => 'image',
                        'label' => __('Banner Image'),
                        'title' => __('Banner Image'),
                    ]
                );
                
                $fieldset->addField(
                    'content',
                    'editor',
                    [
                        'name'  => 'content',
                        'label' => __('Content'),
                        'title' => __('Content'),
                        'config'    => $this->_wysiwygConfig->getConfig()
                    ]
                );
            }
			
			
			return $form;
	}
	
    /**
     * Add category fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @param array $formData
     * @return \Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addCategoryFieldset($form, $formData)
    {
        $categoryTreeBlock = $this->getLayout()->createBlock(
            \Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree::class,
            null,
            ['data' => ['js_form_object' => 'сategoryIds']]
        );

        $catalogFieldset = $form->addFieldset('category_fieldset', []);
		
        $catalogFieldset->addField(
            'category_ids',
            'hidden',
            [
                'name' => 'category_ids',
                'data-form-part' => self::FORM_NAME,
                'after_element_js' => $this->getCategoryIdsJs(),
                'value' =>$formData['category_ids'] 
            ]
        );
		
		$catalogFieldset->addField(
            'show_cat_slide_if_no_image_found',
            'select',
            [
				'label' => __('Show slide which category has no image'),
                'title' => __('Show slide which category has no image'),
                'name' => 'show_cat_slide_if_no_image_found',
                'values' => $this->_yesNo->toOptionArray()
            ]
        );
		
		if (isset($formData['category_ids'])) 
		{
            $categoryTreeBlock->setCategoryIds(explode(',', $formData['category_ids']));
        }

        $catalogFieldset->addField(
            'category_tree_container',
            'note',
            [
                'label' => __('Category'),
                'title' => __('Category'),
                'text' => $categoryTreeBlock->toHtml()
            ]
        );
 
        return $form;
    }
	
	
    /**
     * Add Product fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @param array $formData
     * @return \Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addProductFieldset($form, $formData)
    {
        $productBlock = $this->getLayout()->createBlock(
            ProductGrid::class,
            null,
            ['data' => ['product_ids' => explode(',', $formData['product_ids'])]]
        );

        $productFieldset = $form->addFieldset('product_fieldset', []);
     	 
		$productFieldset->addField(
            'show_prod_slide_if_no_image_found',
            'select',
            [
				'label' => __('Show slide which product has no image'),
                'title' => __('Show slide which product has no image'),
                'name' => 'show_prod_slide_if_no_image_found',
                'value' =>$formData['show_prod_slide_if_no_image_found'] ,
				'values' => $this->_yesNo->toOptionArray()
            ]
        );

        $productFieldset->addField(
            'product_grid_container',
            'note',
            [
                'label' => __('Product'),
                'title' => __('Product'),
                'text' => $productBlock->toHtml()
            ]
        );
	  
	   $productFieldset->addField(
				'product_ids',
				'hidden',
				[
					'name' => 'product_ids',
					'data-form-part' => self::FORM_NAME,
					'after_element_js' => $this->getProductIdsJs($formData['product_ids']),
				]
			);
			
        return $form;
    }

	/**
     * Retrive js code for CategoryIds input field
     *
     * @return string
     */
    private function getCategoryIdsJs()
    {
        return <<<HTML
    <script type="text/javascript">
		require(['jquery'],function($){
			jQuery("#slide_slide_type").on('change',function(){
				var val = jQuery(this).val();
				jQuery("#slide_category_fieldset,#slide_product_fieldset,#slide_image_fieldset").hide()
				if(val == 2 ){
					jQuery("#slide_category_fieldset").show()
				}
				else if(val == 1){
					jQuery("#slide_product_fieldset").show()
				}
				else{
					jQuery("#slide_image_fieldset").show()
				}
				
			})
			jQuery("#slide_slide_type").change()
		})
		
        сategoryIds = {updateElement : {value : "", linkedValue : ""}};
        Object.defineProperty(сategoryIds.updateElement, "value", {
            get: function() {
                return сategoryIds.updateElement.linkedValue
            },
            set: function(v) {
                сategoryIds.updateElement.linkedValue = v;
                jQuery("#slide_category_ids").val(v)
            }
        });
    </script>
HTML;
    }
	private function getProductIdsJs($prod_ids)
    {
        return <<<HTML
    <script type="text/javascript">		 
		  require([
				'mage/adminhtml/grid'
			], function(){
				new serializerController('slide_product_ids', [$prod_ids], [], productsGridJsObject, 'slide_product_ids');
			});				   
    </script>
HTML;
    }
    /**
     * Prepare Slider for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Slide');
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
