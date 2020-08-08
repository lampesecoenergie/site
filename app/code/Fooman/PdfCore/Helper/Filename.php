<?php
namespace Fooman\PdfCore\Helper;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Filename extends \Magento\Framework\App\Helper\AbstractHelper
{
    const DEFAULT_EXT = '.pdf';
    const DEFAULT_MIXED_NAME = 'pdfdocs';

    protected $isMixed = false;
    protected $isRange = false;
    protected $title;
    protected $high;
    protected $low;

    /**
     * @return string
     */
    public function getConstructedTitle()
    {
        if ($this->isMixed) {
            return __('pdfdocs') . self::DEFAULT_EXT;
        }

        if ($this->isRange) {
            return $this->title . '_' . $this->low . '-' . $this->high . self::DEFAULT_EXT;
        }

        if ($this->high) {
            return $this->title . '_' . $this->high . self::DEFAULT_EXT;
        }

        return $this->title . self::DEFAULT_EXT;
    }

    /**
     * @param bool $reset
     *
     * @return mixed
     */
    public function getFilename($reset = false)
    {
        $name = preg_replace('/[^\p{L}\p{N}_\.-]/u', '', $this->getConstructedTitle());
        if ($reset) {
            $this->title = null;
            $this->high = null;
            $this->low = null;
            $this->isMixed = false;
            $this->isRange = false;
        }
        return $name;
    }

    /**
     * Keep track of titles that we are printing
     * The filename should become
     * INVOICE_10000001 on a single invoice
     * INVOICE_10000001-10000007 when printing a range of invoices
     * pdfdocs when multiple types are mixed, for example INVOICE and ORDER
     *
     * @param $title
     * @param $increment
     */
    public function addDocument($title, $increment)
    {
        if ($this->title === null) {
            $this->title = $title;
            $this->high = $increment;
            $this->low = $increment;
        } else {
            $this->isRange = true;
            if ($this->title !== $title) {
                $this->isMixed = true;
            }
            if ($increment > $this->high) {
                $this->high = $increment;
            }
            if ($increment < $this->low) {
                $this->low = $increment;
            }
        }
    }
}
