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
namespace Ves\Themesettings\Block\Adminhtml\System\Config\Form\Field\Font;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * HTML select element block with customer groups options
 */
class FontList extends \Magento\Framework\View\Element\Html\Select
{
	/**
     * Customer groups cache
     *
     * @var array
     */
	private $_fontList;

    /**
     * @var \Ves\Themesettings\Model\System\Config\Source\Css\Font\GoogleFonts
     */
    protected $_fontModel;

    /**
     * @param \Magento\Framework\View\Element\Context                            $context  
     * @param \Ves\Themesettings\Model\System\Config\Source\Css\Font\GoogleFonts $fontList 
     * @param array                                                              $data     
     */
    public function __construct(
    	\Magento\Framework\View\Element\Context $context,
    	\Ves\Themesettings\Model\System\Config\Source\Css\Font\GoogleFonts $fontList,
    	array $data = []
    	) {
    	parent::__construct($context, $data);
    	$this->_fontModel = $fontList;
    }

    /**
     * Retrieve allowed customer groups
     *
     * @param int $groupId return name by customer group id
     * @return array|string
     */
    protected function _getFontList($groupId = null)
    {
    	if ($this->_fontList === null) {
    		$this->_fontList = [];

            foreach ($this->_fontModel->toOptionArray() as $item) {
                $this->_fontList[$item['value']] = $item['label'];    
            }	
    	}
         if ($groupId !== null) {
            return isset($this->_fontList[$groupId]) ? $this->_fontList[$groupId] : null;
        }
    	return $this->_fontList;
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
     * Get the element Html.
     *
     * @return string
     */
    public function getElementHtml()
    {
        $this->addClass('select admin__control-select');

        $html = '';
        if ($this->getBeforeElementHtml()) {
            $html .= '<label class="addbefore" for="' .
                $this->getHtmlId() .
                '">' .
                $this->getBeforeElementHtml() .
                '</label>';
        }

        $html .= '<select id="' . $this->getHtmlId() . '" name="' . $this->getName() . '" ' . $this->serialize(
            $this->getHtmlAttributes()
        ) . $this->_getUiId() . '>' . "\n";

        $value = $this->getValue();
        if (!is_array($value)) {
            $value = [$value];
        }

        if ($values = $this->getValues()) {
            foreach ($values as $key => $option) {
                if (!is_array($option)) {
                    $html .= $this->_optionToHtml(['value' => $key, 'label' => $option], $value);
                } elseif (is_array($option['value'])) {
                    $html .= '<optgroup label="' . $option['label'] . '">' . "\n";
                    foreach ($option['value'] as $groupItem) {
                        $html .= $this->_optionToHtml($groupItem, $value);
                    }
                    $html .= '</optgroup>' . "\n";
                } else {
                    $html .= $this->_optionToHtml($option, $value);
                }
            }
        }

        $html .= '</select>' . "\n";
        if ($this->getAfterElementHtml()) {
            $html .= '<label class="addafter" for="' .
                $this->getHtmlId() .
                '">' .
                "\n{$this->getAfterElementHtml()}\n" .
                '</label>' .
                "\n";
        }
        return $html;
    }

	/**
     * Render block HTML
     *
     * @return string
     */
	public function _toHtml()
	{
		if (!$this->getOptions()) {
			foreach ($this->_getFontList() as $fontId => $fontLabel) {
				$this->addOption($fontId, addslashes($fontLabel));
			}
		}
		return parent::_toHtml();
	}
}