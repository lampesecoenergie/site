/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
require([
    "jquery",
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/prompt',
    "collapsable",
    "prototype"
], function(jQuery, alert, prompt){

    function toggleApplyVisibility(select) {
        if ($(select).value == 1) {
            $(select).next('select').removeClassName('no-display');
            $(select).next('select').removeClassName('ignore-validate');

        } else {
            $(select).next('select').addClassName('no-display');
            $(select).next('select').addClassName('ignore-validate');
            var options = $(select).next('select').options;
            for( var i=0; i < options.length; i++) {
                options[i].selected = false;
            }
        }
    }
    function getFrontTab() {
        if ($('customer_attribute_tabs_front')) {
            return $('customer_attribute_tabs_front').up('li');
        } else {
            return $('front_fieldset-wrapper');
        }
    }

    function checkOptionsPanelVisibility(){
        if($('manage-options-panel')){
            var panel = $('manage-options-panel').up('.fieldset');

            if($('frontend_input') && ($('frontend_input').value=='radio' || $('frontend_input').value=='select' || $('frontend_input').value=='multiselect' || $('frontend_input').value=='checkboxs')){
                panel.show();
            }
            else {
                panel.hide();
            }
        }
    }

    function bindAttributeInputType()
    {
        checkOptionsPanelVisibility();
        switchDefaultValueField();
        showRequiredDefault();
        setRowVisibility('is_wysiwyg_enabled', false);
        setRowVisibility('is_html_allowed_on_front', false);

        switch ($('frontend_input').value) {
            case 'textarea':

                $('frontend_class').value = '';
                $('frontend_class').disabled = true;
                break;
            case 'text':

                if (!$('frontend_class').getAttribute('readonly')) {
                    $('frontend_class').disabled = false;
                }
                break;
            case 'select':
            case 'radio':
            case 'multiselect':
            case 'checkboxs':

                $('frontend_class').value = '';
                $('frontend_class').disabled = true;
                break;
            default:
                $('frontend_class').value = '';
                $('frontend_class').disabled = true;
        }
    }


    function switchDefaultValueField()
    {
        if (!$('frontend_input')) {
            return;
        }

        var currentValue = $('frontend_input').value;

        var defaultValueTextVisibility = false;
        var defaultValueTextareaVisibility = false;
        var defaultValueDateVisibility = false;
        var defaultValueYesnoVisibility = false;
        var show_max_file_size          = false;
        var show_file_extensions        = false;
        var defaultValueFileVisibility = false;

        switch (currentValue) {
            case 'select':
            case 'radio':
                optionDefaultInputType = 'radio';
                break;

            case 'multiselect':
            case 'checkboxs':
                optionDefaultInputType = 'checkbox';
                break;

            case 'date':
                defaultValueDateVisibility = true;
                break;

            case 'file':
                defaultValueFileVisibility = true;
                defaultValueTextVisibility = false;
                defaultValueTextareaVisibility = false;
                defaultValueDateVisibility = false;
                defaultValueYesnoVisibility = false;
                show_max_file_size          = true;
                show_file_extensions        = true;
                break;

            case 'boolean':
                defaultValueYesnoVisibility = true;
                break;

            case 'textarea':
                defaultValueTextareaVisibility = true;
                break;
            default:
                defaultValueTextVisibility = true;
                break;
        }

        setRowVisibility('default_value_text', defaultValueTextVisibility);
        setRowVisibility('default_value_textarea', defaultValueTextareaVisibility);
        setRowVisibility('default_value_date', defaultValueDateVisibility);
        setRowVisibility('default_value_yesno', defaultValueYesnoVisibility);
        setRowVisibility('max_file_size', show_max_file_size);
        setRowVisibility('file_extensions', show_file_extensions);

        /* For Required Field Existing Customer */
        setRowVisibility('default_value_text_required', defaultValueTextVisibility);
        setRowVisibility('default_value_textarea_required', defaultValueTextareaVisibility);
        setRowVisibility('default_value_date_required', defaultValueDateVisibility);
        setRowVisibility('default_value_file_required', defaultValueFileVisibility);
        /* End */

        var elems = document.getElementsByName('default[]');
        for (var i = 0; i < elems.length; i++) {
            elems[i].type = optionDefaultInputType;
        }
    }

    function showRequiredDefault()
    {
        if (!$('frontend_input')) {
            return;
        }
        var show = false;
        if (jQuery('#is_required').val() == 1) {
            show = true;
        }
        var currentValue = $('frontend_input').value;
        /* For Required Field Existing Customer */
        setRowVisibility('default_value_' + currentValue + '_required', show);
        /* End */
    }

    function showDefaultRows()
    {
        setRowVisibility('is_required', true);
        setRowVisibility('frontend_class', true);
    }

    function setRowVisibility(id, isVisible)
    {
        if ($(id)) {
            var td = $(id).parentNode;
            var tr = $(td.parentNode);

            if (isVisible) {
                tr.show();
            } else {
                tr.blur();
                tr.hide();
            }
        }
    }

    function updateRequriedOptions()
    {
        if ($F('frontend_input')=='select' && $F('is_required')==1) {
            $('option-count-check').addClassName('required-options-count');
        } else if ($F('frontend_input')=='radio' && $F('is_required')==1) {
            $('option-count-check').addClassName('required-options-count');
        } else {
            $('option-count-check').removeClassName('required-options-count');
        }
    }



    if($('frontend_input')){
        Event.observe($('frontend_input'), 'change', updateRequriedOptions);
        Event.observe($('frontend_input'), 'change', bindAttributeInputType);
    }

    if ($('is_required')) {
        Event.observe($('is_required'), 'change', updateRequriedOptions);
        Event.observe($('is_required'), 'change', showRequiredDefault);
    }

    jQuery(function($) {
        bindAttributeInputType();
        // @todo: refactor collapsable component
        $('.attribute-popup .collapse, [data-role="advanced_fieldset-content"]')
            .collapsable()
            .collapse('hide');
    });

    window.updateRequriedOptions = updateRequriedOptions;
    window.setRowVisibility = setRowVisibility;
    window.showDefaultRows = showDefaultRows;
    window.switchDefaultValueField = switchDefaultValueField;
    window.bindAttributeInputType = bindAttributeInputType;
    window.checkOptionsPanelVisibility = checkOptionsPanelVisibility;
    window.getFrontTab = getFrontTab;
    window.toggleApplyVisibility = toggleApplyVisibility;
});
