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
namespace Ves\Themesettings\Block\Adminhtml\System\Config\Form\Field;

class Targets extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var \Ves\Themesettings\Model\System\Config\Source\Header\LinkTarget
     */
	protected $_target;

    /**
     * @param \Magento\Framework\View\Element\Context                         $context 
     * @param \Ves\Themesettings\Model\System\Config\Source\Header\LinkTarget $target  
     * @param array                                                           $data    
     */
	public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Ves\Themesettings\Model\System\Config\Source\Header\LinkTarget $target,
        array $data = []
    ) {
    	parent::__construct($context, $data);
    	$this->_target = $target;
	}

     /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

	/**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_target->toOptionArray() as $_type) {
                $this->addOption($_type['label'], addslashes($_type['value']));
            }
        }
        return parent::_toHtml();
    }

}