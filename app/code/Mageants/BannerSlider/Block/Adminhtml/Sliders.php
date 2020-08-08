<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Block\Adminhtml;

class Sliders extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_sliders';
		
        $this->_blockGroup = 'Mageants_BannerSlider';
		
        $this->_headerText = __('Sliders');
		
        $this->_addButtonLabel = __('Create New Slider');
		
        parent::_construct();
    }
}
