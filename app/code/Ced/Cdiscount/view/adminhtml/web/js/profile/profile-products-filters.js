define([
    'Magento_Ui/js/form/element/text'
], function (text) {
    'use strict';

    return text.extend({

        initialize: function () {
            this._super();
            alert('map_____');
            return this;
        }
    });
});