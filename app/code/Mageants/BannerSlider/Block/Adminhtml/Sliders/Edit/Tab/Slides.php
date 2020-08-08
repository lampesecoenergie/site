<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Block\Adminhtml\Sliders\Edit\Tab;

use \Magento\Backend\Block\Template\Context;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Framework\Registry;
use \Mageants\BannerSlider\Model\SlidesFactory;
use \Mageants\BannerSlider\Helper\Data;
use \Mageants\BannerSlider\Model\Source\Status;
use \Mageants\BannerSlider\Model\Source\SlideType;

class Slides extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Country options
     * 
     * @var  \Mageants\BannerSlider\Model\SlidesFactory
     */
    protected $_slidesFactory;
	
	/**
     * Core Object Manager
     * 
	 * @var  \Mageants\BannerSlider\Model\Source\Status
     */
    protected $_objectManager = null;
	
	/**
     * Enable / Disable options
     * 
	 * @var  \Magento\Framework\ObjectManagerInterface
     */
    protected $_status;
	/**
     * Slide Type options
     * 
	 * @var  \Mageants\BannerSlider\Model\Source\SlideType
     */
    protected $_slideType;

	/**
     * Banner Slider Helper 
     * 
	 * @var  \Mageants\BannerSlider\Helper\Data
     */
    protected $_helper = null;
	
    /**
     * constructor
     * 
	 * @param Context $context
     * @param ObjectManagerInterface $objectManager,
	 * @param \Magento\Backend\Helper\Data $backendHelper,
     * @param Registry $registry
     * @param SlidesFactory  $slidesFactory
     * @param Data $helper
	 * @param Status $status,
	 * @param SlideType $slideType,
     * @param array $data
     */
    public function __construct(
        Context $context,
		ObjectManagerInterface $objectManager,
		\Magento\Backend\Helper\Data $backendHelper,
        Registry $registry,
		SlidesFactory $slidesFactory,
		Data $helper,
		Status $status,
		SlideType $slideType,
        array $data = []
    )
    {
		$this->_slidesFactory            = $slidesFactory;
		
		$this->_objectManager 			= $objectManager;
		
		$this->registry = $registry;
		
		$this->_helper = $helper;
		
		$this->_status = $status;
		
		$this->_slideType = $slideType;
		
        parent::__construct($context, $backendHelper, $data);
    }

	/**
     * _construct
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
		
        $this->setId('slidesGrid');
		
        $this->setDefaultSort('id');
		
        $this->setDefaultDir('DESC');
		
        $this->setSaveParametersInSession(true);
		
        $this->setUseAjax(true);
		
		$this->setPagerVisibility(false);
		
        if ($sliderid = $this->getRequest()->getParam('id')) 
		{
            $this->setDefaultFilter(array('slider_id' => $sliderid));
        } 
    }
	
    /**
     * prepare collection
     */
    protected function _prepareCollection()
    {
        $slideFactory = $this->_slidesFactory->create();
		
		$sliderid = $this->getRequest()->getParam('id');
		
		$collection = $slideFactory->getCollection();
		
		$collection->addFieldToFilter('slider_id', array('in' => $sliderid))
					->setOrder('position','DESC');
					
		//if($collection->addFieldToFilter('slider_id', array('in' => $sliderid)));
		
		$this->setCollection($collection);
		
        return $this;
    }
	 
	 /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'image',
            [
                'header' => __('Image'),
                'index' => 'image',
				'renderer' => Renderer\Thumbnail::class,
                'class' => 'xxx',
                'width' => '50px',
				'filter' => false
            ]
        );
		
        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'index' => 'title',
                'class' => 'xxx',
                'width' => '50px',
				'filter' => false
            ]
        );
		
        $this->addColumn(
            'slide_type',
            [
                'header' => __('Slide Type'),
                'index' => 'slide_type',
				'type'  => 'options',
				'options' => $this->_slideType->getOptionArray(),
                'width' => '50px',
				'filter' => false
            ]
        );
		
         $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
				'type'  => 'options',
				'options' => $this->_status->getOptionArray(),
                'class' => 'xxx',
                'width' => '50px',
				'filter' => false
            ]
        );
		
		$this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'index' => 'position',
				'renderer' => Renderer\SlidePosition::class,
                'class' => 'xxx',
                'width' => '50px',
				'filter' => false
            ]
        );
		
		$this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'index' => 'action',
				'renderer' => Renderer\ActionColumn::class,
                'class' => 'xxx',
                'width' => '50px',
				'filter' => false
            ]
        );

        return parent::_prepareColumns();
    }
	
	 /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/slides', ['_current' => true]);
    }
	
	 /**
     * @param  object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }
	
	
    protected function _getSelectedSlides()
    {
         $slides = $this->_slidesFactory->create();
		 
        return $slides->getSlides($slides);
    }
	
	/**
     * Retrieve selected Slides
     *
     * @return array
     */
    public function getSelectedSlides()
    {
		$selected = $this->_getSelectedSlides();

        if (!is_array($selected)) 
		{
            $selected = [];
        }
		
        return array_filter($selected);
    }

    /**
     * Prepare Slider for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Slides');
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
	
    /**
     * Tab is hidden
     *
     * @return string
     */
	public function getMainButtonsHtml()
    {
        $sliderid = $this->getRequest()->getParam('id');
		
		$addButton="";
		
		if($sliderid)
		{
			$newSlideUrl = $this->_helper->getAddSliderSlidesGridUrl($sliderid);
			
			$addButton = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
									->setData(array(
											'label'     => 'Add Slide',
											'onclick'   => "setLocation('{$newSlideUrl}')",
											'class'   => 'action-default scalable save primary ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only '
										))->toHtml();
		}
        return $addButton;
    }
}
