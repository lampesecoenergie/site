<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iksanika\Productmanage\Block\Widget\Grid\Column\Renderer;

/**
 * Backend grid item renderer number
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    
    protected static $showImagesUrl = null;
    protected static $showByDefault = null;
    
    protected static $imagesWidth = null;
    protected static $imagesHeight = null;
    protected static $imagesScale = null;
    
    protected $_helper;
    protected $_imageHelper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Catalog\Helper\Image $catalogImage
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context, 
            \Iksanika\Productmanage\Helper\Data $extensionHelper,
            \Magento\Catalog\Helper\Image $catalogImage,
            array $data = [])
    {
        $this->_helper = $extensionHelper;
        $this->_imageHelper = $catalogImage;
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
    }
    
    public function initSettings()
    {
        if(!self::$showImagesUrl)
            self::$showImagesUrl = (int)$this->_scopeConfig->getValue('iksanika_productmanage/images/showurl') === 1;
        if(!self::$showByDefault)
            self::$showByDefault = (int)$this->_scopeConfig->getValue('iksanika_productmanage/images/showbydefault') === 1;
        if(!self::$imagesWidth)
            self::$imagesWidth = $this->_scopeConfig->getValue('iksanika_productmanage/images/width');
        if(!self::$imagesHeight)
            self::$imagesHeight = $this->_scopeConfig->getValue('iksanika_productmanage/images/height');
        if(!self::$imagesScale)
            self::$imagesScale = $this->_scopeConfig->getValue('iksanika_productmanage/images/scale');
    }

    /**
     * Returns value of the row
     *
     * @param \Magento\Framework\DataObject $row
     * @return mixed|string
     */
    protected function _getValue(\Magento\Framework\DataObject $row)
    {
        $this->initSettings();
        
        $data = parent::_getValue($row);
        
        $noSelection    =   false;
        $dored          =   false;
        if ($getter = $this->getColumn()->getGetter())
        {
            $val = $row->$getter();
        }

        $val = $val2 = $row->getData($this->getColumn()->getIndex());
        $noSelection = ($val == 'no_selection' || $val == '') ? true : $noSelection;
        $url = $this->_helper->getImageUrl($val);
        
        if(!$this->_helper->getFileExists($val)) 
        {
          $dored = true;
          $val .= "[*]";
        }
        
        $dored = (strpos($val, "placeholder/")) ? true : $dored;
        $filename = (!self::$showImagesUrl) ? '' : substr($val2, strrpos($val2, "/")+1, strlen($val2)-strrpos($val2, "/")-1);

        $val = ($dored) ? 
                ("<span style=\"color:red\" id=\"img\">$filename</span>") :
                "<span>". $filename ."</span>";
        
        $out = (!$noSelection) ? 
                ($val. '<center><a href="#" onclick="window.open(\''. $url .'\', \''. $val2 .'\')" title="'. $val2 .'" '. ' url="'.$url.'" id="imageurl">') :
                '';

        $outImagesWidth = self::$imagesWidth ? "width='".self::$imagesWidth."'":'';
        if(self::$imagesScale)
            $outImagesHeight = (self::$imagesHeight) ? "height='".self::$imagesHeight."'":'';
        else
            $outImagesHeight = (self::$imagesHeight && !self::$imagesWidth) ? "height='".self::$imagesHeight."'":'';
        
        try {
            $img  = $this->_imageHelper->init($row, $this->getColumn()->getIndex());
            $imgR = $img->resize(self::$imagesWidth);
//            var_dump($imgR->getResizedImageInfo());
//            die();
            /*
            $out .= (!$noSelection) ?
                    "<img src=".$imgR." ".$outImagesWidth." ".$outImagesHeight." border=\"0\"/>" :
                    "<center><strong>[".__('NO IMAGE')."]</strong></center>";
             */
            $out .= (!$noSelection) ?
//                    "<img src=".$url." ".$outImagesWidth." ".$outImagesHeight." border=\"0\"/>" :
                    "<img src=".$url." border=\"0\"/>" :
                    "<center><strong>[".__('NO IMAGE')."]</strong></center>";
        }catch(\Exception $e)
        {
            $out .= "<center><strong>[".__('NO IMAGE')."]</strong></center>";
        }
        
        return $out. ((!$noSelection)? '</a></center>' : '');
    }

    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
//    public function render(\Magento\Framework\DataObject $row)
    public function render(\Magento\Framework\DataObject $row)
    {
        return $this->_getValue($row);
    }

    /**
     * Renders CSS
     *
     * @return string
     */
    public function renderCss()
    {
        return parent::renderCss() . ' col-number';
    }
    
}
