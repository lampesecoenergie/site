define([
    'jquery',
    'spectrum'
], function (jQuery, colorpicker) {
    'use strict';

    return function (config, element) {
        jQuery(element).spectrum({
            showInitial: true,
            preferredFormat: "hex",
            showInput: true
        });
    };

});