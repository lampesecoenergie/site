<?php
/**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Ui\Component\Listing\Column;

use Mageants\BannerSlider\Model\ResourceModel\Image;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;
use \Mageants\BannerSlider\Model\Source\SlideType;

/**
 * Class Thumbnail
 * @package Aheadworks\Rbslider\Ui\Component\Listing\Columns
 */
class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var ImageFileUploader
     */
    private $_imageFactory;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param ImageFileUploader $imageFileUploader
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Image $imageFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
		
        $this->urlBuilder = $urlBuilder;
		
        $this->_imageFactory = $imageFactory;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) 
		{
			$fieldName = $this->getData('name');

			foreach ($dataSource['data']['items'] as & $item) 
			{
				$configData = $this->getData('config');
				
				$configData['has_preview'] = -1;
				
				$this->setData('config',$configData);
				
				switch($item['slide_type'])
				{
					case SlideType::SLIDE_IMAGE :
					
						$imgUrl = $this->_imageFactory->getBannerUrl($item['image']);                    
						
						$item[$fieldName . '_src'] = $item[$fieldName . '_orig_src'] = $imgUrl;
						
					break;
					
					case SlideType::SLIDE_CATEGORY : 
						
						$imgUrl = $this->_imageFactory->getCategoryTreeIcon();                    
						
						$item[$fieldName . '_src'] = $item[$fieldName . '_orig_src'] = $imgUrl;
						
					break;
					
					case SlideType::SLIDE_PRODUCT : 
						
						$imgUrl = $this->_imageFactory->getProductsIcon();
						
						$item[$fieldName . '_src'] = $item[$fieldName . '_orig_src'] = $imgUrl;
						
					break;
					
				}
				$id = isset($item['slide_id']) ? $item['slide_id'] : $item['id'];
						
				$item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
					'mageants_bannerslider/slides/edit',
					['id' => $id]
				);
								
			}
        }
        return $dataSource;
    }
}
