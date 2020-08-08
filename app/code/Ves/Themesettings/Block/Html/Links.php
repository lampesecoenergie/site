<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Themesettings
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Themesettings\Block\Html;

class Links extends \Magento\Framework\View\Element\Html\Links
{
    /**
     * @var \Ves\Themesettings\Helper\Theme
     */
	public $_ves;
    
    /**
     * @var \Ves\Themesettings\Helper\Data
     */
	public $_vesData;

    public $_vesBlockData;
	
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context 
     * @param \Ves\Themesettings\Helper\Theme                  $ves     
     * @param \Ves\Themesettings\Helper\Data                   $vesData 
     * @param array                                            $data    
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ves\Themesettings\Helper\Theme $ves,
        \Ves\Themesettings\Helper\Data $vesData,
        array $data = []
        ) {
        parent::__construct($context, $data);
        $this->_ves = $ves;
        $this->_vesData = $vesData;
        $this->_vesBlockData = $data;
    }

	/**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {	
        if (isset($this->_vesBlockData['template']) && !empty($this->_vesBlockData['template'])) {
            $this->setTemplate($this->_vesBlockData['template']);
        }
        if(!$this->getTemplate()){
    	   $this->setTemplate("Ves_Themesettings::html/links.phtml");
        }
        return parent::_toHtml();
    }
}