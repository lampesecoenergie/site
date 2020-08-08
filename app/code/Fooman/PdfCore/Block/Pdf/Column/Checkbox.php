<?php
namespace Fooman\PdfCore\Block\Pdf\Column;

use Magento\Framework\Module\Dir;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Checkbox extends \Fooman\PdfCore\Block\Pdf\Column implements \Fooman\PdfCore\Block\Pdf\ColumnInterface
{
    const DEFAULT_WIDTH = 8;
    const DEFAULT_TITLE = '';
    const COLUMN_TYPE = 'fooman_checkbox';

    private $moduleReader;

    private $file;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Filesystem\Io\File $file,
        array $data = []
    ) {
        $this->moduleReader = $moduleReader;
        $this->file = $file;
        parent::__construct($context, $data);
    }

    public function getGetter()
    {
        return [$this, 'getCheckbox'];
    }

    public function getCheckbox()
    {
        //return '☐'; Not available in most fonts
        return sprintf(
            '<img src="@%s" />',
            base64_encode(
                $this->file->read(
                    $this->moduleReader->getModuleDir(Dir::MODULE_VIEW_DIR, 'Fooman_PdfCore')
                    . '/images/tickbox-image.png'
                )
            )
        );
    }
}
