<?php
namespace Fooman\PdfCore\Block\Pdf;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
interface DocumentRendererInterface
{
    /**
     * Prepare html output
     *
     * @return string
     */
    public function renderHtmlTemplate();

    /**
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getScopeConfig();

    /**
     * @return string
     */
    public function getFooterContent();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getIncrement();

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @return \Fooman\PdfCustomiser\Model\IntegratedLabels\Content
     */
    public function getIntegratedLabelsContent();

    /**
     * @return boolean
     */
    public function canApplyIntegratedLabelsContent();

    public function getForcedPageOrientation();
}
