<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Block\Adminhtml;

class Slides extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_slides';
		
        $this->_blockGroup = 'Mageants_BannerSlides';
		
        $this->_headerText = __('Slides');
		
        $this->_addButtonLabel = __('Create New Slide');
		
        parent::_construct();
    }
}
