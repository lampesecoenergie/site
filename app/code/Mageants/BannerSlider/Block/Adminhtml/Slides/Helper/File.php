<?php
 /**
 * @category  Mageants BannerSlider
 * @package   Mageants_BannerSlider
 * @copyright Copyright (c) 2017 Mageants
 * @author    Mageants Team <support@Mageants.com>
 */
namespace Mageants\BannerSlider\Block\Adminhtml\Slides\Helper;

/**
 * @method string getValue()
 * @method bool getDisabled()
 * @method File setExtType(\string $extType)
 */
class File extends \Magento\Framework\Data\Form\Element\File
{
    /**
     * Slide Image model
     * 
     * @var \Rcoktechnolabs\BannerSlider\Model\ResourceModel\Image 
     */
    protected $_imageModel;

    /**
     * constructor
     * 
     * @param \Rcoktechnolabs\BannerSlider\Model\ResourceModel\Image $imageModel
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param array $data
     */
    public function __construct(
        \Mageants\BannerSlider\Model\ResourceModel\Image $imageModel,
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        array $data
    )
    {
        $this->_imageModel = $imageModel;
		
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
		
        $this->setType('file');
		
        $this->setExtType('file');
    }

    /**
     * get the element html
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = '';
		
        $this->addClass('input-file');
		
        $html .= parent::getElementHtml();
		
        if ($this->getValue()) 
		{
            $url = $this->_getUrl();
			
            if (!preg_match("/^http\:\/\/|https\:\/\//", $url)) 
			{
                $url = $this->_imageModel->getBaseUrl() . $url;
            }
			
            $html .= '<br /><a href="'.$url.'">'.$this->_getUrl().'</a> ';
        }
		
        $html .= $this->_getDeleteCheckbox();
		
        return $html;
    }

    /**
     * get the delete checkbox html
     *
     * @return string
     */
    protected function _getDeleteCheckbox()
    {
        $html = '';
		
        if ($this->getValue()) 
		{
            $label = __('Delete File');
			
            $html .= '<span class="delete-image">';
			
            $html .= '<input type="checkbox" name="'.
			
                parent::getName().'[delete]" value="1" class="checkbox" id="'.
				
                $this->getHtmlId().'_delete"'.($this->getDisabled() ? ' disabled="disabled"': '').'/>';
				
            $html .= '<label for="'.$this->getHtmlId().'_delete"'.($this->getDisabled() ? ' class="disabled"' : '').'>';
			
            $html .= $label.'</label>';
			
            $html .= $this->_getHiddenInput();
			
            $html .= '</span>';
        }
        return $html;
    }

    /**
     * get hidden input with the value
     *
     * @return string
     */
    protected function _getHiddenInput()
    {
        return '<input type="hidden" name="'.parent::getName().'[value]" value="'.$this->getValue().'" />';
    }

    /**
     * @return string
     */
    protected function _getUrl()
    {
        return $this->getValue();
    }

    /**
     * get field name
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->getData('name');
    }
}
