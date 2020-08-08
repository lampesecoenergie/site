<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Block\Adminhtml\Sliders\Edit\Tab\Renderer;

use \Magento\Framework\UrlInterface;
use Magento\Backend\Block\Context;

/**
 * Class Thumbnail
 * @package  Mageants\BannerSlider\Block\Adminhtml\Sliders\Edit\Tab\Renderer
 */
class ActionColumn extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
	
    /**
     * Url path  to edit
     * 
     * @var string
     */
    const URL_PATH_EDIT = 'mageants_bannerslider/slides/edit';

    /**
     * Url path  to delete
     * 
     * @var string
     */
    const URL_PATH_DELETE = 'mageants_bannerslider/slides/delete';

    /**
     * URL builder
     * 
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
	
    /**
     * @param Context $context
     * @param ImageFileUploader $imageFileUploader
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
		
        $this->_urlBuilder = $context->getUrlBuilder();
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
		$editUrl = $this->_urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'id' => $row->getId(),
									'sliderid' => $row->getSliderId()
                                ]
                            );
		
		$deleteUrl = $this->_urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'id' => $row->getId(),
                                    'sliderid' => $row->getSliderId()
                                ]
                            );
		
		$edit = "<a href='{$editUrl}' title='Edit'>Edit </a>";
		
		$delete = "<a href='{$deleteUrl}' title='Delete' onclick='return confirm(\"Are you sure to delete ".$row->getTitle()."?\")'>Delete</a>";
		
		return $edit . "<span>|</span> " . $delete;
    }
}
