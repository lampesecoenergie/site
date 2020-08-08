<?php
namespace Fooman\PdfCore\Block\Pdf;

use Magento\Framework\View\Element\Template\Context;

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Table extends PdfAbstract
{
    // phpcs:ignore PSR2.Classes.PropertyDeclaration -- Magento 2 Core use
    protected $_template = 'Fooman_PdfCore::pdf/table.phtml';

    protected $columns;
    protected $columnsMap = [];
    protected $columnsWidth = [];

    protected $tableColumns;
    protected $printHeader = true;

    protected $rtl = false;

    protected $style
        = [
            'header' => ['default' => '', 'first' => '', 'last' => ''],
            'row' => ['default' => '', 'first' => '', 'last' => '', 'odd' => '', 'even' => ''],
            'table' => ['default' => '']
        ];

    public function __construct(Context $context, array $data = [])
    {
        if (!isset($data['tableColumns']) || !is_array($data['tableColumns'])) {
            throw new \InvalidArgumentException('Pdf table columns not defined.');
        }
        $this->tableColumns = $data['tableColumns'];

        if (isset($data['printHeader'])) {
            $this->printHeader = $data['printHeader'];
        }

        if (isset($data['rtl'])) {
            $this->rtl = $data['rtl'];
        } else {
            $this->rtl = false;
        }
        parent::__construct($context, $data);
    }

    public function getColumns()
    {
        if ($this->columns === null) {
            $this->columns = [];
            $i = 0;
            foreach ($this->tableColumns as $tableColumn) {
                if (isset($this->columnsMap[$tableColumn['index']])) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Each column type can only appear once.')
                    );
                }
                if (strpos($tableColumn['index'], 'product/') !== false) {
                    $blockClass = \Fooman\PdfCore\Block\Pdf\Column\ProductAttribute::class;
                    $index = str_replace('product/', '', $tableColumn['index']);
                } else {
                    $blockClass = sprintf(
                        '%s\%s',
                        \Fooman\PdfCore\Block\Pdf\Column::class,
                        ucfirst($tableColumn['index'])
                    );
                    $index = $tableColumn['index'];
                }
                $block = $this->getLayout()->createBlock($blockClass);

                $block->setId($tableColumn['index'])->setIndex($index);
                if (isset($tableColumn['width'])) {
                    $block->setWidthAbs($tableColumn['width']);
                }
                if (isset($tableColumn['title']) && strlen($tableColumn['title']) >= 1) {
                    $block->setTitle($tableColumn['title']);
                }
                if (isset($tableColumn['align'])) {
                    $block->setAlignment($tableColumn['align']);
                }
                $this->setCustomRenderer($block);
                if ($this->getCurrencyCode()) {
                    $block->setCurrencyCode($this->getCurrencyCode());
                }
                if ($this->getBaseCurrencyCode()) {
                    $block->setBaseCurrencyCode($this->getBaseCurrencyCode());
                }
                $this->columns[$i] = $block;
                $this->columnsMap[$tableColumn['index']] = $i;
                $i++;
            }
        }
        return $this->columns;
    }

    /**
     * All fooman_ prefixed block types have a custom column renderer
     * load it here
     *
     * @param $block
     */
    protected function setCustomRenderer($block)
    {
        if (substr($block->getType(), 0, 7) == 'fooman_') {
            $block->setData(
                'renderer',
                sprintf('%s\%s', '\Fooman\PdfCore\Block\Pdf\Column\Renderer', ucfirst(substr($block->getType(), 7)))
            );
        }
    }

    public function getColumnByIndex($index)
    {
        if (empty($this->columns)) {
            $this->getColumns();
        }
        return $this->columns[$this->columnsMap[$index]];
    }

    public function getColumnWidthByIndex($index)
    {
        if (empty($this->columnsWidth)) {
            $this->calculateColumnWidths();
        }
        return $this->columnsWidth[$index];
    }

    /**
     * Set collection
     *
     * @param array $collection
     *
     * @return void
     */
    public function setCollection(array $collection)
    {
        $this->setData('dataSource', $collection);
    }

    /**
     * Get collection
     *
     * @return array
     */
    public function getCollection()
    {
        return $this->getData('dataSource');
    }

    /**
     * @return bool
     */
    public function shouldPrintHeader()
    {
        return $this->printHeader;
    }

    protected function calculateColumnWidths()
    {
        $totalWidth = 0;
        foreach ($this->getColumns() as $column) {
            $totalWidth += $column->getWidthAbs();
        }
        if ($totalWidth > 0) {
            $widthFactor = 100 / $totalWidth;
        } else {
            $widthFactor = 1;
        }

        foreach ($this->getColumns() as $column) {
            $this->columnsWidth[$column->getIndex()] = $widthFactor * $column->getWidthAbs();
        }
    }

    public function getAlign($isFirst, $isLast)
    {
        if ($this->rtl) {
            return $isFirst ? 'right' : ($isLast ? 'left' : 'center');
        }
        return $isFirst ? 'left' : ($isLast ? 'right' : 'center');
    }

    public function getHeaderStyle($isFirst, $isLast)
    {
        return $this->getStyle('header', $isFirst, $isLast);
    }

    public function getRowStyle($isFirst, $isLast)
    {
        return $this->getStyle('row', $isFirst, $isLast);
    }

    public function getCellStyle($isFirstRow, $isLastRow, $isFirstCell, $isLastCell)
    {
        return $this->getStyle('row', $isFirstRow, $isLastRow). ' '. $this->getStyle('cell', $isFirstCell, $isLastCell);
    }

    public function getTableStyle()
    {
        return $this->getStyle('table', false, false);
    }

    public function getStyle($type, $isFirst, $isLast)
    {
        if ($isFirst && $isLast) {
            return trim($this->style[$type]['first'] . ' '. $this->style[$type]['last']);
        }

        if ($isFirst) {
            return $this->style[$type]['first'];
        }

        if ($isLast) {
            return $this->style[$type]['last'];
        }
        return $this->style[$type]['default'];
    }

    public function setStyling(array $style)
    {
        $this->style = array_replace_recursive($this->style, $style);
    }

    public function getRowBg(\Magento\Framework\DataObject $item, $pos)
    {
        if ($pos % 2 === 0) {
            return $this->style['row']['even'];
        }
        return $this->style['row']['odd'];
    }

    /**
     * If item has extras this will be displayed as separate row
     *
     * @param $item
     *
     * @return bool
     */
    public function hasExtras(\Magento\Framework\DataObject $item)
    {
        return false;
    }

    /**
     * @param $item
     *
     * @return string
     */
    public function getExtras(\Magento\Framework\DataObject $item)
    {
        return '';
    }
}
