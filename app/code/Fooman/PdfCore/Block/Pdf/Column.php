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
class Column extends \Magento\Backend\Block\Widget\Grid\Column
{
    const DEFAULT_WIDTH = 20;
    const DEFAULT_TITLE = '';
    const COLUMN_TYPE = 'default';

    protected $title;
    protected $widthAbs;
    protected $currencyCode;
    protected $baseCurrencyCode;

    /**
     * get type of column
     *
     * @return string
     */
    public function getType()
    {
        return static::COLUMN_TYPE;
    }

    /**
     * get title for column
     *
     * @return string
     */
    public function getTitle()
    {
        if (null === $this->title) {
            $default = static::DEFAULT_TITLE;
            return __($default);
        }
        return __($this->title);
    }

    /**
     * set title for column
     *
     * @param $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    public function setCurrencyCode($currency)
    {
        $this->currencyCode = $currency;
        return $this;
    }

    public function getBaseCurrencyCode()
    {
        return $this->baseCurrencyCode;
    }

    public function setBaseCurrencyCode($currency)
    {
        $this->baseCurrencyCode = $currency;
        return $this;
    }

    public function getRate()
    {
        //since we use the already exchanged rate set it to 1 to prevent a second exchange rate calculation
        return 1;
    }

    public function getUseOrderCurrency()
    {
        return true;
    }

    public function isDisplayingBothCurrencies()
    {
        return
            !empty($this->baseCurrencyCode)
            && $this->baseCurrencyCode !== $this->currencyCode;
    }

    /**
     * get absolute width of column
     *
     * @return int
     */
    public function getWidthAbs()
    {
        if (null === $this->widthAbs) {
            return static::DEFAULT_WIDTH;
        } else {
            return $this->widthAbs;
        }
    }

    /**
     * set absolute width of column
     *
     * @param $width
     *
     * @return $this
     */
    public function setWidthAbs($width)
    {
        $this->widthAbs = $width;
        return $this;
    }

    public function convertInterfaceConstantToGetter($input)
    {
        return 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $input)));
    }

    public function getOrderItem($row)
    {
        if ($row instanceof \Magento\Sales\Api\Data\OrderItemInterface) {
            return $row;
        }
        return $row->getOrderItem();
    }
}
