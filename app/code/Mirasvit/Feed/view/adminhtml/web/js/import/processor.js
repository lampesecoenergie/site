define([
    'jquery',
    'uiComponent'
], function($, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            formId: null
        },

        process: function(actionUrl) {
            $(this.formId)
                .attr('action', actionUrl)
                .submit();
        }
    });
});