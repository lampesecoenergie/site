<?php

namespace Fooman\PdfCore\Model\Config\Source;

use Magento\Framework\App\Filesystem\DirectoryList;
use Fooman\PdfCore\Model\Config\Backend\Customfont;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Fonts implements \Magento\Framework\Data\OptionSourceInterface
{

    private $mediaDir;

    private $preLoadedFonts;

    private $file;

    private $ioFile;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\Filesystem\Io\File $ioFile
    ) {
        $this->mediaDir = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->file = $file;
        $this->ioFile = $ioFile;
        $this->preLoadedFonts = [
            'courier'        => __('Courier'),
            'times'          => __('Times New Roman'),
            'helvetica'      => __('Helvetica'),
            'dejavusans'     => __('DejaVuSans'),
            'dejavusansmono' => __('DejaVuSansMono'),
            'dejavuserif'    => __('DejaVuSerif'),
            'cid0cs'         => __('System Font - Chinese Simplified'),
            'cid0ct'         => __('System Font - Chinese Traditional'),
            'cid0jp'         => __('System Font - Japanese'),
            'cid0kr'         => __('System Font - Korean')
        ];
    }

    public function getPreLoadedFonts()
    {
        return $this->preLoadedFonts;
    }

    /**
     * supply dropdown choices for fonts
     * generated from contents of lib/tcpdf/fonts directory
     * and uploaded custom fonts
     *
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function toOptionArray()
    {
        $returnArray = [];
        foreach ($this->preLoadedFonts as $fontname => $label) {
            $returnArray[] = ['value' => $fontname, 'label' => $label];
        }

        $returnArray = [['label' => __('Default Fonts'), 'value' => $returnArray]];

        $customFontPath = $this->mediaDir->getAbsolutePath(Customfont::PATH_CUSTOMFONTS);
        $fontsToAdd = [];
        if ($this->file->isExists($customFontPath)) {
            foreach (new \DirectoryIterator($customFontPath) as $fontFile) {
                if (!$fontFile->isDot() && $this->ioFile->getPathInfo($fontFile)['extension'] === 'php') {
                    $filename = $this->ioFile->getPathInfo($fontFile)['filename'];
                    if (substr($filename, -1) !== 'b') {
                        $fontsToAdd[] = ['value' => $filename, 'label' => $filename];
                    }
                }
            }
        }

        if (!empty($fontsToAdd)) {
            $returnArray[] = ['label' => __('Custom Fonts'), 'value' => $fontsToAdd];
        }

        return $returnArray;
    }
}
