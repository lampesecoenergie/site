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
class SlidePosition extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
		
        $slideid = $row->getId();
		
        $position = $row->getPosition();
		
		$input = "<input type='number' value='{$position}' style='width:50px' name='slidepositions[{$slideid}]'>";
		
		return  $input;
    }
}
