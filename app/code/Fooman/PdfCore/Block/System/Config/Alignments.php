<?php
namespace Fooman\PdfCore\Block\System\Config;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Alignments extends \Magento\Framework\View\Element\Html\Select
{

    // phpcs:ignore PSR2.Methods.MethodDeclaration -- Magento 2 Core use
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->toOptionArray());
        }
        return parent::_toHtml();
    }

    /**
     * supply dropdown choices for custom product attributes
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('Automatic')],
            ['value' => 'left', 'label' => __('Left')],
            ['value' => 'center', 'label' => __('Center')],
            ['value' => 'right', 'label' => __('Right')],

        ];
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
