<?php
namespace Fooman\PdfCore\Model\Response\Http;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class FileFactory extends \Magento\Framework\App\Response\Http\FileFactory
{
    /**
     * @param Response                      $response
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        Response $response,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->_response = $response;
        $this->_filesystem = $filesystem;
    }
}
