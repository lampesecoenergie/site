define([
    'prototype',
    'underscore',
    'mageUtils',
    'mage/translate'
], function (prototype, _, utils, $t) {
    'use strict';

    function MyConstructor(config) {
    }

    MyConstructor.prototype.initContainer = function () {
    };

    MyConstructor.prototype.collectGridData = function (action, data) {
        var itemsType = data.excludeMode ? 'excluded' : 'selected',
            selections = {};

        selections[itemsType] = data[itemsType];

        if (!selections[itemsType].length) {
            selections[itemsType] = false;
        }

        _.extend(selections, data.params || {});

        // START - Fooman added
        $$('.col-tracking-number input, .col-tracking-carrier select').each(function (el) {
            var valueToSend;
            if (el.value.length === 0) {
                valueToSend = $t('N/A');
            } else {
                valueToSend = el.value;
            }

            selections[el.readAttribute('name')] = valueToSend;
        });
        // END - Fooman added

        utils.submit({
            url: action.url,
            data: selections
        });
    };

    return MyConstructor;
});
