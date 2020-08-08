<?php
namespace Fooman\PdfCore\Helper;

use Fooman\PdfCore\Model\Config\Backend\Customfont;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Font extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Fooman\PdfCore\Model\Config\Source\Fonts
     */
    private $fontChoices;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $mediaDir;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Fooman\PdfCore\Model\Config\Source\Fonts $fontChoices,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->fontChoices = $fontChoices;
        $this->filesystem = $filesystem;
        parent::__construct($context);
    }

    public function getFontFile($font)
    {
        return $this->isDefaultFont($font) ? null : $this->getVarDir()->getAbsolutePath(
            Customfont::PATH_CUSTOMFONTS . $font . '.php'
        );
    }

    public function isDefaultFont($font)
    {
        return isset($this->fontChoices->getPreLoadedFonts()[$font]);
    }

    private function getVarDir()
    {
        if (null === $this->mediaDir) {
            $this->mediaDir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        }
        return $this->mediaDir;
    }
}
