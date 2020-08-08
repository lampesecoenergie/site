<?php
namespace Fooman\PdfCore\Helper;

use Magento\Framework\Filesystem\Io\File;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class FileOps
{
    /**
     * @var File
     */
    private $file;

    public function __construct(
        File $file
    ) {
        $this->file = $file;
    }

    /**
     * @param $filename
     *
     * @return bool
     */
    public function fileExists($filename)
    {
        if (!$this->proceedForFile($filename)) {
            return false;
        }

        return $this->file->fileExists($filename);
    }

    /**
     * @param $filename
     *
     * @return bool
     */
    private function proceedForFile($filename)
    {
        return !(strpos($filename, 'phar://') === 0);
    }
}
