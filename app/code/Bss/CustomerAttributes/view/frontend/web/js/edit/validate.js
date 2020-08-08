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
define([
    "jquery",
    "mage/mage"
], function ($) {
    'use strict';

    $.widget('mage.validationDob', {
        options: {
        },
        _create: function () {
            var self = this;
            var dataForm = $(self.element);
            var ignore = this.options.ignore;
            var dobEnable = this.options.dobEnable;
            if (dobEnable) {
                dataForm.mage('validation', {
                    errorPlacement: function (error, element) {
                        if (element.prop('id').search('full') !== -1) {
                            var dobElement = $(element).parents('.customer-dob'),
                                errorClass = error.prop('class');
                            error.insertAfter(element.parent());
                            dobElement.find('.validate-custom').addClass(errorClass)
                                .after('<div class="' + errorClass + '"></div>');
                        } else {
                            error.insertAfter(element);
                        }
                    },
                    ignore: ':hidden:not(' + ignore + ')'
                });
            } else {
                dataForm.mage('validation', {
                    ignore: ignore ? ':hidden:not(' + ignore + ')' : ':hidden'
                });
            }
        }
    });
    return $.mage.validationDob;
});
