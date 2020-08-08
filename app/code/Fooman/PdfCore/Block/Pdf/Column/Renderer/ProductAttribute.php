<?php
namespace Fooman\PdfCore\Block\Pdf\Column\Renderer;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ProductAttribute extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    private $currencyFormatter;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->currencyFormatter = $priceCurrency;
        parent::__construct($context, $data);
    }

    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return mixed
     */
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore -- Magento 2 Core use
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        $value = parent::_getValue($row);
        if (isset($value['render_as_currency'])) {
            return $this->renderAsCurrency($row, $value['value']);
        }

        return $this->escapeHtml($value, ['br']);
    }

    private function renderAsCurrency($row, $value)
    {
        if (is_array($value)) {
            $output = [];
            foreach ($value as $price) {
                if ($price['amount'] !== null) {
                    $output[] = $this->currencyFormatter->format(
                        $price['amount'],
                        null,
                        null,
                        null,
                        $price['currency']
                    );
                }
            }
            return implode('<br/>', $output);
        }

        if ($value) {
            return $this->currencyFormatter->format($value, null, null, null, $this->getCurrencyCode($row));
        }
    }

    private function getCurrencyCode($row)
    {
        if ($code = $this->getColumn()->getCurrencyCode()) {
            return $code;
        }
        if ($code = $row->getData($this->getColumn()->getCurrency())) {
            return $code;
        }
        return null;
    }
}
