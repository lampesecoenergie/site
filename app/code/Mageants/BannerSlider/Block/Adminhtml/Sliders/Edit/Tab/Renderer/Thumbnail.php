<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Block\Adminhtml\Sliders\Edit\Tab\Renderer;

use Mageants\BannerSlider\Model\ResourceModel\Image;
use Magento\Backend\Block\Context;
use \Mageants\BannerSlider\Model\Source\SlideType;

/**
 * Class Thumbnail
 * @package  Mageants\BannerSlider\Block\Adminhtml\Sliders\Edit\Tab\Renderer
 */
class Thumbnail extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * @var ImageFileUploader
     */
    private $_imageFactory;

    /**
     * @param Context $context
     * @param ImageFileUploader $imageFileUploader
     * @param array $data
     */
    public function __construct(
        Context $context,
        Image $imageFactory,
        array $data = []
    ) 
	{
        parent::__construct($context, $data);
		
        $this->_imageFactory = $imageFactory;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
		switch($row->getSlideType())
		{
			case SlideType::SLIDE_IMAGE :
			
				$imgUrl = $this->_imageFactory->getBannerUrl($row->getImage());				
				
			break;
			
			case SlideType::SLIDE_CATEGORY : 
				
				$imgUrl = $this->_imageFactory->getCategoryTreeIcon();                    
							
			break;
			
			case SlideType::SLIDE_PRODUCT : 
				
				$imgUrl = $this->_imageFactory->getProductsIcon();
				
			break;
			
		}
		
        return '<img width="200" src="' . $imgUrl . '"/>';
    }
}
