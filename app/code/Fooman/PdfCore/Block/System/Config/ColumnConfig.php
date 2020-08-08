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
class ColumnConfig extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    protected $columnsRenderer;

    protected $alignmentRenderer;

    protected $excludes = [];

    /**
     * Prepare to render
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    // phpcs:ignore PSR2.Methods.MethodDeclaration -- Magento 2 Core use
    protected function _prepareToRender()
    {
        $this->addColumn(
            'columntype',
            [
                'label'    => __('Type'),
                'renderer' => $this->getColumnsRenderer()
            ]
        );
        $this->addColumn('title', ['label' => __('Custom Title')]);
        $this->addColumn('width', ['label' => __('Custom Width')]);
        $this->addColumn('align', [
            'label' => __('Alignment'),
            'renderer' => $this->getAlignmentRenderer()
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Column');
    }

    /**
     * provide hash of current value so it gets preselected
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    // phpcs:ignore PSR2.Methods.MethodDeclaration -- Magento 2 Core use
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->getColumnsRenderer()->calcOptionHash($row->getData('columntype'))]
            = 'selected="selected"';
        $optionExtraAttr['option_' . $this->getAlignmentRenderer()->calcOptionHash($row->getData('align'))]
            = 'selected="selected"';
        $row->setData('option_extra_attrs', $optionExtraAttr);
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getColumnsRenderer()
    {
        if (null === $this->columnsRenderer) {
            $element = $this->getElement();
            $uniqId = hash('sha256', $element->getHtmlId() . $element->getScope() . $element->getScopeId());
            $this->columnsRenderer = $this->getLayout()->createBlock(
                Columns::class,
                'fooman_pdfcore_system_config_columns_' . $uniqId,
                ['data' => ['is_render_to_js_template' => true, 'excludes' => $this->excludes]]
            );
        }
        return $this->columnsRenderer;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAlignmentRenderer()
    {
        if (null === $this->alignmentRenderer) {
            $element = $this->getElement();
            $uniqId = hash('sha256', $element->getHtmlId() . $element->getScope() . $element->getScopeId());
            $this->alignmentRenderer = $this->getLayout()->createBlock(
                Alignments::class,
                'fooman_pdfcore_system_config_align_' . $uniqId,
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->alignmentRenderer;
    }

    public function getHtmlId()
    {
        return $this->getElement()->getHtmlId();
    }
}
