<?php

namespace Fooman\PdfCore\Model\Tcpdf;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Defaults
{
    protected $filesystem;
    protected $storeManager;

    const FACTOR_PIXEL_PER_MM = 3;

    /**
     * @param \Magento\Framework\Filesystem              $filesystem
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;

        if (!defined('K_TCPDF_EXTERNAL_CONFIG')) {
            $cacheDir = $this->filesystem->getDirectoryWrite(DirectoryList::CACHE);
            $cacheDir->create('pdfcache/');

            define('K_TCPDF_EXTERNAL_CONFIG', true);
            define('K_PATH_MAIN', $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath());
            define('K_PATH_URL', $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA));
            define('PDF_HEADER_LOGO', '');
            define('PDF_HEADER_LOGO_WIDTH', 30);
            define('K_PATH_CACHE', $cacheDir->getAbsolutePath('pdfcache/'));
            define('K_BLANK_IMAGE', '_blank.png');
            define('K_TCPDF_CALLS_IN_HTML', true);
            define('K_TCPDF_THROW_EXCEPTION_ERROR', true);
            define(
                'K_PATH_FONTS',
                $this->filesystem->getDirectoryWrite(DirectoryList::ROOT)->getAbsolutePath(
                    'vendor/fooman/tcpdf/fonts/'
                )
            );
        }
    }
}
