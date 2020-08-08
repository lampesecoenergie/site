define([
    'uiRegistry',
    'Magento_Ui/js/form/element/ui-select',
    'jquery',
    'underscore'
], function (uiRegistry, Select, $, _) {
    'use strict';

    return Select.extend({
        defaults: {
            optgroupTmpl: 'Ced_Amazon/grid/filters/elements/ui-select-optgroup',
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
                var categoryPath = data.value;
                if (!this.empty(categoryPath)) {
                    // TODO: validate
                    var categoryIds = categoryPath.split("_");
                    var category = uiRegistry.get('index = profile_category');
                    // category.value(categoryIds[0]);
                    category.value(categoryPath);
                    var marketplace = uiRegistry.get('index = marketplace');
                    var account = uiRegistry.get('index = account_id');
                    var barcode = uiRegistry.get('index = barcode_exemption');
                    // For Block Attribute rendering
                    this.updateAttributes(account.value, categoryIds[0], categoryIds[1], marketplace.value(), barcode.value());
                }
            } else {
                this.value(_.without(this.value(), data.value));
            }
            this.listVisible(true);
            return this;
        },

        updateAttributes: function (accountId, categoryId, subCategoryId, marketplaceIds, barcode) {
            var parameters = {
                'profile_id': MARKETPLACE_PROFILE_ID,
                "category_id": categoryId,
                "sub_category_id": subCategoryId,
                "account_id": accountId,
                "marketplace_ids": marketplaceIds,
                "barcode_exemption": barcode,
                'form_key': window.FORM_KEY
            };

            $.ajax({
                url: MARKETPLACE_ATTRIBUTE_UPDATE_URL,
                type: 'POST',
                data: parameters,
                dataType: 'html',
                showLoader: true,
                success: function (response) {
                    $('#attribute-amazon-attributes-container').html(response);
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
        },

        empty: function (e) {
            switch (e) {
                case "":
                case 0:
                case "0":
                case null:
                case false:
                    return true;
                default:
                    if (typeof e === "undefined") {
                        return true;
                    } else if (typeof e === "object" && Object.keys(e).length === 0){
                        return true;
                    } else {
                        return false;
                    }
            }
        }
    });
});
