<?php
namespace Fooman\PdfCustomiser\Model;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ControllerConfig
{
    const XML_PATH_PRINTORDER = 'sales_pdf/shipment/shipmentuseorder';

    private $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function shouldPrintOrderAsPackingSlip()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_PRINTORDER);
    }
}
