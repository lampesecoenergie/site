<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Block\Adminhtml\Slides\Edit\Tab;

use \Magento\Backend\Block\Template\Context;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Backend\Helper\Data;
use \Magento\Framework\Registry;
use \Mageants\BannerSlider\Model\SlidesFactory;
		
class Slides extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Wysiwyg config
     * 
     * @var Config
     */
    protected $_wysiwygConfig;

    /**
     * Slides Model
     * 
     * @var SlidesFactory
     */
    protected $_slidesFactory;
	
	
    protected $_objectManager = null;
    /**
     * constructor
     * 
     * @param Config $wysiwygConfig
	 * @param ObjectManagerInterface $objectManager,
     * @param Context $context
     * @param Registry $registry
     * @param SlidesFactory  $slidesFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
		ObjectManagerInterface $objectManager,
		Data $backendHelper,
        Registry $registry,
		SlidesFactory $slidesFactory,
        array $data = []
    )
    {
		$this->_slidesFactory            = $slidesFactory;
		
		$this->_objectManager 			= $objectManager;
		
		$this->registry = $registry;

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
        
		if ($sliderId = $this->getRequest()->getParam('id')) 
		{
            $this->setDefaultFilter(array('slider_id' => $sliderId));
        } 
    }

    /**
     * prepare collection
     */
    protected function _prepareCollection()
    {
        $collection = $this->_slidesFactory->create();
			
		$this->setCollection($collection->getCollection());
		
		return parent::_prepareCollection();
    }
	 /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'type' => 'hidden',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        ); 
        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'index' => 'title',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
         $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        $this->addColumn(
            'updated_at',
            [
                'header' => __('Modified Date'),
                'type' => 'dateRange',
                'index' => 'updated_at',
                'width' => '50px',
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
	
	 /**
     * @return array
     */
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
        return $selected;
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
}
