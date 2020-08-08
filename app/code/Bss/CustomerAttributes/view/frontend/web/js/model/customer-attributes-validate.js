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
define(
    [
        'jquery',
        'mage/translate',
        'mage/url'
    ],
    function ($, $t, urlManager) {
        'use strict';

        return {
            validate: function () {
                var flag = true,
                    self = this,
                    requireField = window.checkoutConfig.bssCA.requireField,
                    errorHtml = "<div role='alert' class='field-error'><span>" + $t('This is a required field.') + "</span></div>";

                $.each(requireField, function (index, value) {
                    if ($(value).length) {
                        if ($(value).attr('type') == 'checkbox') {
                            $(value).each(function () {
                                if($(this).is(':checked')) {
                                    flag = true;
                                    return false;
                                } else {
                                    flag = false;
                                }
                            })
                            if (flag == false) {
                                if (!$(value + ':last').parent().find('.field-error').length) {
                                    $(value + ':last').parent().append(errorHtml);
                                }
                            } else {
                                $(value + ':last').parent().find('.field-error').remove();
                            }
                        } else {
                            if ($(value).val() == null || $(value).val() == '') {
                                if (!$(value).parent().find('.field-error').length) {
                                    $(value).parent().append(errorHtml);
                                }
                                flag = false;
                            } else {
                                $(value).parent().find('.field-error').remove();
                            }
                        }
                    }
                });
                if (flag == true) {
                    var extension_attributes = {};
                    $('input[name*=bss_customer_attributes], textarea[name*=bss_customer_attributes], select[name*=bss_customer_attributes]').each(function () {
                        var selfChild = this;
                        extension_attributes = self.setNameAttributes(selfChild, extension_attributes);
                    });
                    $.ajax({
                        url: urlManager.build('customerattribute/save/index'),
                        data: extension_attributes,
                        type: "POST",
                        dataType: 'json'
                    }).done(function (data) {

                    });
                }
                return flag;
            },

            setNameAttributes: function (selfChild, extension_attributes) {
                var name = $(selfChild).attr("name");
                var name = name.replace('bss_customer_attributes[', '');
                var name = name.replace(']', '');
                if ($(selfChild).attr("type") == 'radio') {
                    if ($(selfChild).prop("checked")) {
                        extension_attributes[name] = $(selfChild).val();
                    }
                } else if ($(selfChild).attr("type") == 'checkbox') {
                    if ($(selfChild).prop("checked")) {
                        if (typeof extension_attributes[name] === "undefined")
                            extension_attributes[name] = [];
                        extension_attributes[name].push($(selfChild).val());
                    }
                } else {
                    extension_attributes[name] = $(selfChild).val();
                }

                return extension_attributes;
            }
        }
    }
);