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


namespace Mirasvit\Feed\Export\Filter;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Mirasvit\Core\Helper\Image;
use Magento\Framework\DataObject;

class ImageFilter
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Image
     */
    protected $image;

    /**
     * @param Filesystem $filesystem
     * @param Image      $image
     */
    public function __construct(
        Filesystem $filesystem,
        Image $image
    ) {
        $this->filesystem = $filesystem;
        $this->image = $image;
    }

    /**
     * Resize image
     *
     * @param string $input
     * @param int    $width
     * @param int    $height
     *
     * @return string
     */
    public function resize($input, $width = null, $height = null)
    {
        $media = $this->filesystem->getUri(DirectoryList::MEDIA);
        $paths = explode($media, $input);

        if (count($paths) == 2) {
            $image = $paths[1];

            return (string)$this->image->init(new DataObject(), null, null, $image)
                ->resize($width, $height);
        }

        return $input;
    }
}