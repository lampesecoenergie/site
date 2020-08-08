<?php

namespace Potato\ImageOptimization\Model\Source\Image;

use Magento\Framework\Data\OptionSourceInterface;
use Potato\ImageOptimization\Model\Optimization\Image\Gif as GifImage;
use Potato\ImageOptimization\Model\Optimization\Image\Jpeg as JpegImage;
use Potato\ImageOptimization\Model\Optimization\Image\Png as PngImage;

class Type implements OptionSourceInterface
{
    const IMAGE_TYPE_REGEXP = '/\.(png|jpe{0,1}g|gif)$/i';

    const GIF_IMAGE_TYPE = 'image/gif';
    const JPEG_IMAGE_TYPE = 'image/jpeg';
    const PNG_IMAGE_TYPE = 'image/png';
    
    /**
     * @return array
     */
    public function getOptionArray()
    {
        return [
            self::GIF_IMAGE_TYPE => GifImage::class,
            self::JPEG_IMAGE_TYPE => JpegImage::class,
            self::PNG_IMAGE_TYPE => PngImage::class,
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->getOptionArray();
        $result = [];
        foreach ($options as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }
        return $result;
    }

    /**
     * @param string $imageType
     * @return string|null
     */
    public function getClassByImageType($imageType)
    {
        $options = $this->getOptionArray();
        if (isset($options[$imageType])) {
            return $options[$imageType];
        }
        return null;
    }

    /**
     * @param string $imagePath
     * @return int
     */
    public function getImageType($imagePath)
    {
        if (!preg_match(self::IMAGE_TYPE_REGEXP, $imagePath) || !file_exists($imagePath)) {
            return null;
        }
        if(function_exists('mime_content_type')) {
            return mime_content_type($imagePath);
        }
        $mimeTypes = array(
            'png'  => 'image/png',
            'jpe'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg'  => 'image/jpeg',
            'gif'  => 'image/gif',
            'bmp'  => 'image/bmp',
            'ico'  => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif'  => 'image/tiff',
            'svg'  => 'image/svg+xml',
            'svgz' => 'image/svg+xml'
        );
        $pathExtension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        if (array_key_exists($pathExtension, $mimeTypes)) {
            return $mimeTypes[$pathExtension];
        }
        return null;
    }
}
