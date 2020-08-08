<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Themesettings
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Themesettings\Block\Adminhtml\System\Config\Form\Field;

class Font extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{   
    /**
     * @var \Ves\Themesettings\Model\System\Config\Source\Css\Font\GoogleFonts
     */
    protected $_fontModel;

    /**
     * @var \Ves\Themesettings\Model\System\Config\Source\Css\Font\Groupcustomgoogle
     */
    protected $_groupcustomgoogle;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param GroupManagementInterface $fontList
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Ves\Themesettings\Model\System\Config\Source\Css\Font\GoogleFonts $fontList,
        \Ves\Themesettings\Model\System\Config\Source\Css\Font\Groupcustomgoogle $groupcustomgoogle
        ) {
        parent::__construct($context);
        $this->_fontModel = $fontList;
        $this->_groupcustomgoogle = $groupcustomgoogle;
    }

    public function _construct(){
        parent::_construct();
    }

    /**
     * @var Customergroup
     */
    protected $_groupRenderer;

    /**
     * Retrieve HTML markup for given form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {

        $isCheckboxRequired = $this->_isInheritCheckboxRequired($element);

        // Disable element if value is inherited from other scope. Flag has to be set before the value is rendered.
        if ($element->getInherit() == 1 && $isCheckboxRequired) {
            $element->setDisabled(true);
        }

        $html = '';
        /*$html = '<td class="label"><label for="' .
            $element->getHtmlId() .
            '">' .
            $element->getLabel() .
            '</label></td>';*/

            $value = $element->getValue();
            if(isset($value['__empty'])){
                unset($value['__empty']);
                $element->setValue($value);
            }
            $html .= $this->_renderValue($element);

            if ($isCheckboxRequired) {
                $html .= $this->_renderInheritCheckbox($element);
            }

            $html .= '<span' .$this->_renderScopeLabel($element) . '></span>';
            $html .= $this->_renderHint($element);
            $script = '';

            $script = '<script>
            require(["jquery", "prototype"],function(){
                
                var googleFonts = '.json_encode($this->_fontModel->getGooglesFonts()).';

                jQuery(document).ready(function() {
                    var elementIds = [];
                    function preview(obj){
                        var val = jQuery(obj).val();
                         var cssProperty = jQuery(obj).data("preview-property");

                        if( (cssProperty=="font-family" && val!="--select--" && googleFonts.indexOf(val) >= 0) || cssProperty!="font-family" ){
                            var key = val.toLowerCase().replace(" ","");
                            var ss = document.createElement("link");
                            ss.type = "text/css";
                            ss.rel = "stylesheet";
                            ss.href = "http://fonts.googleapis.com/css?family=" + val;
                            if(elementIds.length==0 || jQuery.inArray(key, elementIds) == -1){
                                elementIds[elementIds.length] = key;
                                document.getElementsByTagName("head")[0].appendChild(ss);
                            }
                            var id = jQuery(obj).data("preview-id");
                            console.log(cssProperty + "|" + val);
                            jQuery("#"+id).css(cssProperty,val);
                        }
                    }

                    jQuery(document).on("change keyup keydown",".font-preview", function(){
                                preview(this);
                            }).change();
                        setTimeout(function(){
                            jQuery(".font-preview").each(function(){
                                preview(this);
                            });
                        },200);
                            jQuery(document).on("change keyup keydown",".font-preview", function(){
                                preview(this);
                            });
                        });
                    });
                    </script>';
                return $this->_decorateRowHtml($element, $html).$script;
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @return string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName){
        $columns = $this->getColumns();
        $column = $columns[$columnName];
        $inputName = $this->_getCellInputElementName($columnName);
        if(isset($column['type'])){
            switch ($column['type']) {
                case 'textarea':
                $html = '<textarea type="text" id="' . $this->_getCellInputElementId(
                    '<%- _id %>',
                    $columnName
                    ) .
                '"' .
                ' name="' .
                $inputName .
                '" value="<%- ' .
                $columnName .
                ' %>" ' .
                ($column['size'] ? 'size="' .
                    $column['size'] .
                    '"' : '') .
                ' class="' .
                (isset(
                    $column['class']
                    ) ? $column['class'] : 'input-text') . '"' . (isset(
                    $column['style']
                    ) ? ' style="' . $column['style'] . '"' : '') . '></textarea>'.$column['comment'];
                    return $html;
                    break;
                    case 'select':
                    $html = '<select '.$column['attributes'].' data-preview-id="' . $this->_getCellInputElementId('<%- _id %>', "preview" ) . '" id="' . $this->_getCellInputElementId(
                        '<%- _id %>',
                        $columnName
                        ) .
                    '" name="' . $inputName . '" ' .
                    ($column['size'] ? 'size="' . $column['size'] . '"' : '') . ' class="' .
                    (isset($column['class']) ? $column['class'] : 'input') . '"'.
                    (isset($column['style']) ? ' style="'.$column['style'] . '"' : '') . '>';

                    $options = $column['values'];
                    if(is_array($options)){
                        foreach ($options as $_font) {
                            //$html .= '<option value="'.$_font['value'].'">'.$_font['label'].'</option>';
                            $html .= $this->_optionToHtml($_font);
                        }
                    }
                    $html .= '</select>';
                    $html .= '<div id="' . $this->_getCellInputElementId('<%- _id %>', "preview" ) . '" style="font-size:18px; margin-top:5px;position: absolute;height: 60px;">The quick brown fox jumps over the lazy dog</div>';
                    return $html;
                    break;
                    case 'text':
                    $html = '<input data-preview-id="' . $this->_getCellInputElementId('<%- _id %>', "preview" ) . '" '.$column['attributes'].' ' . $this->_getCellInputElementId('<%- _id %>', "preview" ) . '" type="text" id="' . $this->_getCellInputElementId(
                        '<%- _id %>',
                        $columnName
                        ) .
                    '"' .
                    ' name="' .
                    $inputName .
                    '" value="<%- ' .
                    $columnName .
                    ' %>" ' .
                    ($column['size'] ? 'size="' .
                        $column['size'] .
                        '"' : '') .
                    ' class="' .
                    (isset(
                        $column['class']
                        ) ? $column['class'] : 'input-text') . '"' . (isset(
                        $column['style']
                        ) ? ' style="' . $column['style'] . '"' : '') . '/>';
                        return $html;
                        break;
                        default:
                        break;
                    }
                }
                return parent::renderCellTemplate($columnName);
            }

        /**
     * Format an option as Html
     *
     * @param array $option
     * @param array $selected
     * @return string
     */
        public function _optionToHtml($option)
        {   
            $class = $html = '';
            if(isset($option['class'])){
                $class = 'class="'.$option['class'].'"';
            }
            if (is_array($option['value'])) {
                $html = '<optgroup '.$class.' label="' . $option['label'] . '">';
                foreach ($option['value'] as $groupItem) {
                    $html .= $this->_optionToHtml($groupItem);
                }
                $html .= '</optgroup>';
            } else {
                $html = '<option '.$class.'  value="' . $option['value'] . '"';
                $html .= '>' . $option['label'] . '</option>';
            }
            return $html;
        }

    /**
     * Render element value
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($element->getTooltip()) {
            $html = '<td class="value with-tooltip">';
            $html .= $this->_getElementHtml($element);
            $html .= '<div class="tooltip"><span class="help"><span></span></span>';
            $html .= '<div class="tooltip-content">' . $element->getTooltip() . '</div></div>';
        } else {
            $html = '<td class="value" style="width:100%" colspan="3">';
            //$html .= '<div style="color: #303030;float: none;font-size: 14px;padding-bottom: 10px;font-weight: 600;">'.$element->getLabel().'</div>';
            $html .= $this->_getElementHtml($element);
        }
        if ($element->getComment()) {
            $html .= '<p class="note"><span>' . $element->getComment() . '</span></p>';
        }
        $html .= '</td>';
        return $html;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        // Add more font
        $googleFonts = $this->_fontModel->getOptions();
        $groupcustomgoogle = $this->_groupcustomgoogle->toOptionArray();
        unset($groupcustomgoogle[0]);
        unset($groupcustomgoogle[1]);
        $fonts = array_merge($groupcustomgoogle, $googleFonts);
        array_unshift($fonts,['label'=>'--Select--','value'=>'']);

        $new = [
        ['label' => __('Fonts'), 'value' => $groupcustomgoogle, 'class' => 'cfonts'],
        ['label' => __('Google Fonts'), 'value' => $googleFonts, 'class' => 'googlefonts'],
        ];
        $this->addColumn('font', [
            'label' => __('Custom, Google Fonts'),
            'class' => 'font-preview',
            'type' => 'select',
            'values' => $new,
            'attributes' => 'data-preview-property="font-family"',
            'style' => 'width:170px;'
            ]);
        $this->addColumn('size', [
            'label' => __('Font Size'),
            'class' => 'font-preview',
            'type' => 'text',
            'style' => 'width:80px;',
            'attributes' => 'data-preview-property="font-size"'
            ]);
        $this->addColumn('weight', [
            'label' => __('Font Weight'),
            'class' => 'font-preview',
            'type' => 'text',
            'style' => 'width:80px;',
            'attributes' => 'data-preview-property="font-weight"'
            ]);
        $this->addColumn('classes', [
            'label' => __('Font Targets'),
            'type' => 'textarea',
            'style'=>'position: relative;',
            'comment' => __('Comma-separated. For example: h1,h2,h3,h4,h5,h6,p... ')
            ]);
        $this->_addAfter = false;   
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Add a column to array-grid
     *
     * @param string $name
     * @param array $params
     * @return void
     */
    public function addColumn($name, $params)
    {
        $this->_columns[$name] = [
        'label' => $this->_getParam($params, 'label', 'Column'),
        'size' => $this->_getParam($params, 'size', false),
        'style' => $this->_getParam($params, 'style'),
        'type' => empty($params['type'])  ? null    : $params['type'],
        'class' => $this->_getParam($params, 'class'),
        'attributes' => empty($params['attributes'])  ? null    : $params['attributes'],
        'values'    => empty($params['values'])  ? null    : $params['values'],
        'comment'    => empty($params['comment'])  ? null    : $params['comment'],
        'renderer' => false,
        ];
        if (!empty($params['renderer']) && $params['renderer'] instanceof \Magento\Framework\View\Element\AbstractBlock) {
            $this->_columns[$name]['renderer'] = $params['renderer'];
        }
    }
}