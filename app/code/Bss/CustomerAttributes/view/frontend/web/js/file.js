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
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($) {
    return function (config) {
        if (config.fileSize != "bss_nothing") {
            //Validate Image FileSize
            var maxSizeKb = config.fileSize;
            var maxSizeMagento = Math.round(config.maxSizeMagento/1024);
            if (maxSizeKb > maxSizeMagento) {
                maxSizeKb = maxSizeMagento;
            }
            $.validator.addMethod(
                config.validatorSize, function (v, elm) {
                    if (navigator.appName == "Microsoft Internet Explorer") {
                        if (elm.value) {
                            var oas = new ActiveXObject("Scripting.FileSystemObject");
                            var e = oas.getFile(elm.value);
                            var size = e.size;
                        }
                    } else {
                        if (elm.files[0] != undefined) {
                            size = elm.files[0].size;
                        }
                    }
                    if (size != undefined && size > maxSize || size > maxSizeMagento) {
                        return false;
                    }
                    return true;
                }, $.mage.__('The file size should not exceed '+maxSizeKb+'Kb'));
        }

        if (config.fileExtension != "bss_nothing") {
            //Validate Image Extensions
            var fileExtension = config.fileExtension;
            $.validator.addMethod(
                config.validatorExtensions, function (v, elm) {

                    var extensions = fileExtension.split(',');
                    if (!v) {
                        return true;
                    }
                    with (elm) {
                        var ext = value.substring(value.lastIndexOf('.') + 1);
                        for (i = 0; i < extensions.length; i++) {
                            if (ext == extensions[i]) {
                                return true;
                            }
                        }
                    }
                    return false;
                }, $.mage.__('Allowed input type are ' + fileExtension));
        }
    };
});
