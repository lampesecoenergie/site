<?php
namespace Fooman\PdfCustomiser\Block;

/**
 * Pdf rendering class for terms and conditions
 *
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Agreement extends \Fooman\PdfCore\Block\Pdf\DocumentRenderer
{

    const LAYOUT_HANDLE = 'fooman_pdfcustomiser_agreement';
    const PDF_TYPE = 'agreement';

    /**
     * return array of variables to be passed to the template
     *
     * @return array
     */
    public function getTemplateVars()
    {
        return array_merge(
            parent::getTemplateVars(),
            ['agreement' => $this->getAgreement()]
        );
    }

    public function getStoreId()
    {
        return $this->getAgreement()->getStoreId();
    }

    protected function getTemplateText()
    {
        $templateText = sprintf(
            '{{layout handle="%s"',
            static::LAYOUT_HANDLE
        );

        $templateVars = array_keys($this->getTemplateVars());
        foreach ($templateVars as $var) {
            $templateText .= ' ' . $var . '=$' . $var;
        }
        $templateText .= '}}';
        return $templateText;
    }

    public function getTitle()
    {
        return $this->getAgreement()->getName();
    }

    public function getIncrement()
    {
        return '';
    }
}
