<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Controller\Adminhtml;

use \Mageants\BannerSlider\Model\SlidesFactory;
use \Magento\Framework\Registry;
use \Magento\Backend\Model\View\Result\RedirectFactory;
use \Magento\Backend\App\Action\Context;

abstract class Slides extends \Magento\Backend\App\Action
{
    /**
     * Slides Factory
     * 
     * @var \Mageants\BannerSlider\Model\SlidesFactory
     */
    protected $_slidesFactory;

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
     * @param SlidesFactory $slidesFactory
     * @param Registry $coreRegistry
     * @param RedirectFactory $resultRedirectFactory
     * @param Context $context
     */
    public function __construct(
        SlidesFactory $slidesFactory,
        Registry $coreRegistry,
        Context $context
    )
    {
        $this->_slidesFactory           = $slidesFactory;
		
        $this->_coreRegistry          = $coreRegistry;
		
        $this->_resultRedirectFactory = $context->getResultRedirectFactory();
		
        parent::__construct($context);
    }

    /**
     * Init Slides
     *
     * @return \Mageants\BannerSlider\Model\Slides
     */
    protected function _initSlides()
    {
        $slideid  = (int) $this->getRequest()->getParam('id');
		
        /** @var \Mageants\BannerSlider\Model\Slides $slides */
		
        $slides    = $this->_slidesFactory->create();
		
        if ($slideid) 
		{
            $slides->load($slideid);
        }
		
        $this->_coreRegistry->register('mageants_bannerslider_slides', $slides);
		
        return $slides;
    }
}
