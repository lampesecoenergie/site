<?php
namespace Fooman\PdfCore\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class PdfFileHandling
{
    /**
     * @var \Fooman\PdfCore\Model\Response\Http\FileFactory
     */
    private $fileFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * PdfFileHandling constructor.
     *
     * @param Response\Http\FileFactory     $fileFactory
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Fooman\PdfCore\Model\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
    }

    public function sendPdfFile(
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        $dir = DirectoryList::VAR_DIR,
        $remove = true
    ) {
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $dir */
        $dir = $this->filesystem->getDirectoryWrite($dir);
        $dir->writeFile($pdfRenderer->getFileName(), $pdfRenderer->getPdfAsString());
        $content = [
            'type' => 'filename',
            'value' => $pdfRenderer->getFileName(),
            'rm' => $remove
        ];

        return $this->fileFactory->create(
            $pdfRenderer->getFileName(),
            $content,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
