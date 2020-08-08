<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Block\Adminhtml\Sliders\Edit\Tab;

use \Magento\Cms\Model\Wysiwyg\Config;
use \Magento\Backend\Block\Template\Contextl;
use \Magento\Framework\Registry;
use \Magento\Framework\Data\FormFactory;
use \Magento\Cms\Ui\Component\Listing\Column\Cms\Options;
use \Magento\Config\Model\Config\Source\Yesno;

abstract class Tabs  extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Wysiwyg config
     * 
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * Store View options
     * 
     */
    protected $_cmsOpt;
	
    /**
     * Yes No options
     * 
     */
    protected $_yesNo;

    /**
     * constructor
     * 
     * @param Config $wysiwygConfig
     * @param Context $context
	 * @param Options $cmsOpt
	 * @param Yesno $yesNo
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
		\Magento\Cms\Ui\Component\Listing\Column\Cms\Options $cmsOpt,
		\Magento\Config\Model\Config\Source\Yesno $yesNo,
        array $data = []
    )
    {
        $this->_wysiwygConfig            = $wysiwygConfig;
        
		$this->_cmsOpt 					 = $cmsOpt;
		
        $this->_yesNo 					 = $yesNo;
		
		parent::__construct($context, $registry, $formFactory, $data);
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
