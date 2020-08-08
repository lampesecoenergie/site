<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Controller\Adminhtml;

use \Mageants\BannerSlider\Model\SlidersFactory;
use \Magento\Framework\Registry;
use \Magento\Backend\Model\View\Result\RedirectFactory;
use \Magento\Backend\App\Action\Context;
		
abstract class Sliders extends \Magento\Backend\App\Action
{
    /**
     * Sliders Factory
     * 
     * @var \Mageants\BannerSlider\Model\SlidersFactory
     */
    protected $_slidersFactory;

    /**
     * Core registry
     * 
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Result redirect factory
     * 
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $_resultRedirectFactory;

    /**
     * constructor
     * 
     * @param SlidersFactory $slidersFactory
     * @param Registry $coreRegistry
     * @param RedirectFactory $resultRedirectFactory
     * @param Context $context
     */
    public function __construct(
        SlidersFactory $slidersFactory,
        Registry $coreRegistry,
        Context $context
    )
    {
        $this->_slidersFactory           = $slidersFactory;
		
        $this->_coreRegistry          = $coreRegistry;
		
        $this->_resultRedirectFactory = $context->getResultRedirectFactory();
		
        parent::__construct($context);
    }

    /**
     * Init Slides
     *
     * @return \Mageants\BannerSlider\Model\Sliders
     */
    protected function _initSliders()
    {
        $sliderid  = (int) $this->getRequest()->getParam('id');
		
        /** @var \Mageants\BannerSlider\Model\Sliders $sliders */
        $sliders    = $this->_slidersFactory->create();
		
        if ($sliderid) 
		{
            $sliders->load($sliderid);
        }
		
        $this->_coreRegistry->register('mageants_bannerslider', $sliders);
		
        return $sliders;
    }
}
