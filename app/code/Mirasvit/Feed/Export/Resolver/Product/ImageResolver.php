<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.0.103
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Export\Resolver\Product;

use Magento\Catalog\Model\Product;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Calculation as TaxCalculation;
use Magento\Framework\UrlInterface;

class ImageResolver extends AbstractResolver
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Return full url to image
     *
     * @param Product $product
     * @return string
     */
    public function getImage($product)
    {
        if ($product->getImage()) {
            return $this->getImageUrl($product, $product->getImage());
        }

        return '';
    }

    /**
     * Return full url to image
     *
     * @param Product $product
     * @return string
     */
    public function getThumbnail($product)
    {
        if ($product->getThumbnail()) {
            return $this->getImageUrl($product, $product->getThumbnail());
        }

        return '';
    }
    
      /**
     * Return full url to image
     *
     * @param Product $product
     * @return string
     */
    public function getSmallImage($product)
    {
        if ($product->getSmallImage()) {
            return $this->getImageUrl($product, $product->getSmallImage());
        }

        return '';
    }
    
    /**
     * Return list of gallery images
     *
     * @param Product $product
     * @param [] $args - possible keys:
     * - int size - used to limit size of gallery images
     *
     * @return array
     */
    public function getGallery($product, $args = [])
    {
        $gallery = [];

        $galleryImages = $product->getMediaGalleryImages();

        /** @var \Magento\Framework\DataObject $galleryImage */
        if (is_array($galleryImages) || $galleryImages instanceof \Traversable) {
            foreach ($galleryImages as $galleryImage) {
                $gallery[] = $this->getImageUrl($product, $galleryImage->getData('file'));
            }
        }

        if (isset($args[0]) && is_numeric($args[0])) {
            return array_slice($gallery, 0, $args[0]);
        }

        return $gallery;
    }

    /**
    * Return all gallery images
    *
    * @param Product $product
    * @return string
    */
    public function getImages($product)
    {
        $galleryCollection = $this->getGallery($product);

        return implode(", ", $galleryCollection);
    }

    /**
     * All this magic for return image url without CDN
     *
     * @param Product $product
     * @param string $file
     * @return string
     */
    protected function getImageUrl($product, $file)
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA, false);
        $baseUrl = rtrim($baseUrl, '/');

        $file = ltrim(str_replace('\\', '/', $file), '/');

        $url = $baseUrl . '/' . $product->getMediaConfig()->getBaseMediaUrlAddition() . '/' . $file;

        return $url;
    }

}
