<?php
/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magetop
 * @package     Magetop_Productslider
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

namespace Magetop\Productslider\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magetop\Productslider\Model\SliderFactory;

/**
 * Class Slider
 * @package Magetop\Productslider\Controller\Adminhtml
 */
abstract class Slider extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magetop_Productslider::slider';

    /**
     * Slider Factory
     *
     * @var \Magetop\Productslider\Model\SliderFactory
     */
    protected $_sliderFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Slider constructor.
     * @param \Magetop\Productslider\Model\SliderFactory $sliderFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        Context $context,
        SliderFactory $sliderFactory,
        Registry $coreRegistry
    )
    {
        $this->_sliderFactory = $sliderFactory;
        $this->_coreRegistry  = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * Init Slider
     *
     * @return \Magetop\Productslider\Model\Slider
     */
    protected function _initSlider()
    {
        $slider   = $this->_sliderFactory->create();

        $sliderId = (int)$this->getRequest()->getParam('id');
        if ($sliderId) {
            $slider->load($sliderId);
        }
        $this->_coreRegistry->register('magetop_productslider_slider', $slider);

        return $slider;
    }
}
