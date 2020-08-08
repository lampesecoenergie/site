<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iksanika\Productmanage\Helper;


/**
 * Catalog data helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected static $showImageBase = null;
    protected static $showImageSmall = null;
    protected static $showImageThumbnail = null;

    public static $columnSettings = array();
    public static $_scopeConfig = array();
    
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $productAttrCollection,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,

        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Catalog\Api\Data\ProductLinkInterfaceFactory $productLinkFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_productAttrCollection = $productAttrCollection;
        $this->_localeCurrency = $localeCurrency;

        $this->_mediaConfig = $mediaConfig;
        $this->productLinkFactory = $productLinkFactory;
        $this->productRepository = $productRepository;
    }

    public static function initSettings()
    {
        self::$showImageBase        =   (!self::$showImageBase) ? ((int)$this->_scopeConfig->getValue('iksanika_productmanage/images/image_base') === 1) : null;
        self::$showImageSmall       =   (!self::$showImageSmall) ? ((int)$this->_scopeConfig->getValue('iksanika_productmanage/images/image_small') === 1) : null;
        self::$showImageThumbnail   =   (!self::$showImageThumbnail) ? (int)$this->_scopeConfig->getValue('iksanika_productmanage/images/image_thumbnail') === 1 : null;
    }
    
    public function showImageBase()
    {
        self::initSettings();
        return self::$showImageBase;
    }
    
    public function showImageSmall()
    {
        self::initSettings();
        return self::$showImageSmall;
    }
    
    public function showImageThumbnail()
    {
        self::initSettings();
        return self::$showImageThumbnail;
    }
    
    public function getImageUrl($image_file)
    {
        $url = false;
        //$url .= '/pub/media/catalog/product'.$image_file;
//        $url = $this->_storeManager->getStore()->getBaseUrl();
//        $url .= 'pub/media/catalog/product'.$image_file;
        $url = $this->_mediaConfig->getMediaUrl($image_file);
        return $url;
    }
    
    public function getFileExists($image_file)
    {
        $file_exists = false;
        $file_exists = file_exists('pub/media/catalog/product'. $image_file);
        return $file_exists;
    }

    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
    
    public function getStore()
    {
        return $this->_storeManager->getStore($this->getStoreId());
    }

    
    // get list of all products attributes
    public function getAttributesList()
    {
        $attributes = $this->_productAttrCollection
                         ->addVisibleFilter()
                         ->addStoreLabel($this->getStoreId());
        $attributes->getSelect();
        return $attributes;
    }
    
    
    public function getGridAttributes()
    {
//        $selected = (string) Mage::getStoreConfig('productupdater/attributes/positions' . Mage::getSingleton('admin/session')->getUser()->getId());
//        return ($selected) ? explode(',', $selected) : array();
    }
    
    public function getSelectedAttributes()
    {
//        return $this->getGridAttributes();
    }

    
    
    public function getDelimiter()
    {
//        $formattedPrice = Mage::app()->getLocale()->currency(null)->toCurrency(1);
        $formattedPrice = $this->_localeCurrency->getCurrency(null)->toCurrency(1, array('display' => \Zend_Currency::NO_SYMBOL));
        return strpos($formattedPrice, '.') ? '.' : ',';
    }
    
    public function recalculatePrice($originalPrice, $newPrice)
    {
        $delimiter  =   '.'; // ,
        $delimiter  =   $this->getDelimiter();
        $newPrice   =   str_replace($delimiter == '.' ? ',' : '.', '', $newPrice);
        
//        if (!preg_match('/^[0-9]+(\.[0-9]+)?$/', $newPrice))
        if(!preg_match('/^[0-9]+(\\'.$delimiter.'[0-9]+)?$/', $newPrice))
        {
//            if (!preg_match('/^[+-][0-9]+(\.[0-9]+)?%?$/', $newPrice))
            if(!preg_match('/^[+-][0-9]+(\\'.$delimiter.'[0-9]+)?%?$/', $newPrice))
            {
                throw new \Exception(__('Please provide the difference as +5'.$delimiter.'25 -5'.$delimiter.'25 +5'.$delimiter.'25% or -5'.$delimiter.'25%')); 
            }else
            {
                $sign       =   substr($newPrice, 0, 1);
                $newPrice   =   substr($newPrice, 1);
                $percent    =   (substr($newPrice, -1, 1) == '%');
                if ($percent)
                    $newPrice = substr($newPrice, 0, -1);

                $newPrice = floatval($newPrice);
                if ($newPrice < 0.00001)
                {
                    throw new \Exception(__('Please provide a non empty difference'));
                }

                $value = $percent ? ($originalPrice * $newPrice / 100) : $newPrice;

                if($sign == '+')
                {
                    $value = $originalPrice + $value;
                }else
                if($sign == '-')
                {
                    $value = $originalPrice - $value;
                }
                return $value;
            }
        }else
        {
            return $newPrice;
        }
    }

    
    public static function prepareColumnSettings() 
    {
        $scopeConfig = self::$_scopeConfig;
        $storeSettings = $scopeConfig->getValue('iksanika_productmanage/columns/showcolumns');
//        $storeSettings = Mage::helper('productupdater/profile')->getCurrentProfile()->columns_showcolumns;
        
        $tempArr = explode(',', $storeSettings);
        
        foreach($tempArr as $showCol) 
        {
            self::$columnSettings[trim($showCol)] = true;
        }
    }
    
    public static function getColumnSettings()
    {
        if(count(self::$columnSettings) == 0)
        {
            self::prepareColumnSettings();
        }
        return self::$columnSettings;
    }
    
    public static function getColumnForUpdate()
    {
        $fields = array('product');
        
        if(count(self::getColumnSettings()))
        {
            foreach(self::getColumnSettings() as $columnId => $status)
            {
//                if(isset(self::$columnType[$columnId]))
                {
                    $fields[] = $columnId;
                }
            }
        }
        return $fields;
    }
    
    public function colIsVisible($code) 
    {
        $columnSettings = self::getColumnSettings();
        return isset($columnSettings[$code]);
    }
    
    public static function setScopeConfig($scopeConfig)
    {
        self::$_scopeConfig = $scopeConfig;
    }
    
    public static function getScopeConfig()
    {
        return self::$_scopeConfig;
    }
    
    public function getRelatedLinks($productIds, $existProducts, $productId)
    {
        $link = [];
        foreach ($productIds as $relatedToId) 
        {
            if ($productId != $relatedToId) 
            {
                $link[$relatedToId] = ['position' => null];
            }
        }
        // Fetch and append to already related products.
        foreach($existProducts as $existProduct)
        {
            $link[$existProduct->getId()] = ['position' => null];
        }
        return $link;
    }
    
    












    public function removeProductLinksByType($product, $productType = 'related') // related / upsell / crosssell
    {
        $productLinksResult = [];
        $productLinks = $product->getProductLinks();
        foreach($productLinks as $link)
        {
            if($link->getLinkType() != $productType)
            {
                $productLinksResult[] = $link;
            }
        }
        return $productLinksResult;
    }


    public function addProductsLinksByType($product, $linkToProductsIds, $relationType = 'related') // related / upsell / crosssell
    {
        //$links = $this->getLinkResolver()->getLinks();
        $links = [$relationType => $linkToProductsIds];

        // $product->setProductLinks([]);
        // $product = $this->productLinks->initializeLinks($product, $links);
        $productLinks = $product->getProductLinks();

        if($relationType == 'related')
        {
            $linkTypes = [$relationType => $product->getRelatedReadonly()];
        }else
        if($relationType == 'upsell')
        {
            $linkTypes = [$relationType => $product->getUpsellReadonly()];
        }else
        if($relationType == 'crosssell')
        {
            $linkTypes = [$relationType => $product->getCrosssellReadonly()];
        }

        foreach ($linkTypes as $linkType => $readonly) {
            if (isset($links[$linkType]) && !$readonly) {
                foreach ((array) $links[$linkType] as $linkProductId) {
                    if(empty($linkProductId) || ($product->getId() == $linkProductId)) {
                        continue;
                    }

                    $linkProduct = $this->getProductRepository()->getById((int)$linkProductId);
                    $link = $this->productLinkFactory->create();

                    $link->setSku($product->getSku())
                        ->setLinkedProductSku($linkProduct->getSku())
                        ->setLinkType($linkType)
                        ->setPosition(0);
                        //->setPosition(isset($linkData['position']) ? (int)$linkData['position'] : 0);

                    $productLinks[] = $link;
                }
            }
        }

        return $productLinks;
    }

    /**
     * @return ProductRepository
     */
    private function getProductRepository()
    {
        if (null === $this->productRepository) {
            $this->productRepository = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Catalog\Api\ProductRepositoryInterface\Proxy');
        }
        return $this->productRepository;
    }

    
    
    
}
