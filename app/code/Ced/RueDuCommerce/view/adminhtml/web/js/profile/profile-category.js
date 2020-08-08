define([
    'Magento_Ui/js/form/element/ui-select',
    'jquery'
], function (Select, $) {
    'use strict';

    return Select.extend({
        defaults: {
            optgroupTmpl: 'Ced_RueDuCommerce/grid/filters/elements/ui-select-optgroup',
            multiple: true

        },

        /**
         * Parse data and set it to options.
         *
         * @param {Object} data - Response data object.
         * @returns {Object}
         */
        setParsed: function (data) {
            var option = this.parseData(data);

            if (data.error) {
                return this;
            }

            this.options([]);
            this.setOption(option);
            this.set('newOption', option);
        },

        /**
         * Normalize option object.
         *
         * @param {Object} data - Option object.
         * @returns {Object}
         */
        parseData: function (data) {
            return {
                'is_active': data.category['is_active'],
                level: data.category.level,
                value: data.category['entity_id'],
                label: data.category.name,
                parent: data.category.parent
            };
        },

        /**
         * Toggle activity list element
         *
         * @param {Object} data - selected option data
         * @returns {Object} Chainable
         */
        toggleOptionSelected: function (data) {
            var isSelected = this.isSelected(data.value);

            if (this.lastSelectable && data.hasOwnProperty(this.separator)) {
                return this;
            }

            if (!isSelected) {
                this.value([]);
                this.value.push(data.value);
                this.updateAttributes(data.value); // For Block Attribute rendering
            } else {
                this.value(_.without(this.value(), data.value));
            }
            this.listVisible(true);
            return this;
        },

        updateAttributes: function (id) {
            var parameters = {
                'profile_id': LAZADA_PROFILE_ID,
                "categories": {
                    "category_1": id
                },
                'form_key': window.FORM_KEY
            };

            $.ajax({
                url: LAZADA_ATTRIBUTE_UPDATE_URL,
                type: 'POST',
                data: parameters,
                dataType: 'html',
                showLoader: true,
                success: function (response) {
                    $('#attribute-rueducommerceAttributes-container').html(response);
                }
            });

        },

        /**
         * Check selected option
         *
         * @param {String} value - option value
         * @return {Boolean}
         */
        isSelected: function (value) {
            return this.value() == value;
        }

    });
});
