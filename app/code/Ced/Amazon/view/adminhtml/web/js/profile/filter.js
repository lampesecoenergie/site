define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract',
    'jquery'
], function (_, uiRegistry, Abstract, $) {
    'use strict';

    return Abstract.extend({
        addFilter: function (action, data) {
            var itemsType = data.excludeMode ? 'excluded' : 'selected',
                selections = {};

            selections[itemsType] = data[itemsType];

            if (!selections[itemsType].length) {
                selections[itemsType] = false;
            }

            _.extend(selections, data.params || {});

            // TODO: recheck all cases.
            if (!data.excludeMode) {
                var fields = ["placeholder", "store_id"];

                $.each(selections['filters'], function (i, v) {
                    if (fields.indexOf(i) < 0) {
                        delete selections['filters'][i]
                    }
                });
            }

            var filter = uiRegistry.get('index = filter');
            filter.value(JSON.stringify(selections));
        }
    });
});