<?php
namespace Fooman\PdfCore\Block\Pdf\Template;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Logo extends \Fooman\PdfCore\Block\Pdf\Block
{
    // phpcs:ignore PSR2.Classes.PropertyDeclaration -- Magento 2 Core use
    protected $_template = 'Fooman_PdfCore::pdf/logo.phtml';

    protected $logoHelper;
    protected $storeId;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Fooman\PdfCore\Helper\Logo $logoHelper,
        array $data = []
    ) {
        $this->logoHelper = $logoHelper;
        $this->storeId = $data['storeId'];
        parent::__construct($context, $data);
    }

    public function getLogoPath()
    {
        $this->logoHelper->initLogo($this->storeId);
        return $this->logoHelper->getLogoPath();
    }

    public function getLogoDimensions($maxHeight = 25)
    {
        $this->logoHelper->initLogo($this->storeId);
        return $this->logoHelper->getLogoDimensions($maxHeight);
    }
}
