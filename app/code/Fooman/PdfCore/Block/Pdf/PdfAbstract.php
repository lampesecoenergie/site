<?php
namespace Fooman\PdfCore\Block\Pdf;

use Magento\Framework\View\Element\Template;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
abstract class PdfAbstract extends \Magento\Framework\View\Element\Template
{
    const XML_PATH_FONT = 'sales_pdf/all/allfont';
    const XML_PATH_FONT_SIZE = 'sales_pdf/all/allfontsize';
    const XML_PATH_BARCODE_TYPE = 'sales_pdf/all/allbarcode';

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $design;

    public function __construct(Template\Context $context, array $data = [])
    {
        $this->design = $context->getDesignPackage();
        parent::__construct($context, $data);
    }

    public function getTemplateFile($template = null)
    {
        $params = [
            'module' => $this->getModuleName(),
            'store_id' =>$this->getStoreId(),
            'theme'=> $this->design->getDesignTheme()->getThemePath()
        ];
        $area = $this->getArea();
        if ($area) {
            $params['area'] = $area;
        }
        return $this->resolver->getTemplateFileName($template ?: $this->getTemplate(), $params);
    }

    /**
     * @param string $size (optional) normal | large | small
     *
     * @return float
     * @access public
     */
    public function getFontsize($size = 'normal')
    {
        $fontSize = $this->_scopeConfig->getValue(
            self::XML_PATH_FONT_SIZE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        switch ($size) {
            case 'large':
                return $fontSize * 1.5;
            case 'bigger':
                return $fontSize * 1.2;
            case 'small':
                return $fontSize * ($fontSize < 12 ? 1 : 0.8);
            case 'normal':
            default:
                return $fontSize;
        }
    }

    public function getBarcodeType()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_BARCODE_TYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
