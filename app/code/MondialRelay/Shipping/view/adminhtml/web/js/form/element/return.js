/**
 * @author    Agence Dn'D <contact@dnd.fr>
 * @copyright 2019 Agence Dn'D
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      https://www.dnd.fr/
 */

define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function ($, _, uiRegistry, select, modal) {
    'use strict';

    return select.extend({
        /**
         * Init
         */
        initialize: function () {
            this._super();

            this.fieldDepend(this.value());

            return this;
        },

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            this.fieldDepend(value);

            return this._super();
        },

        /**
         * Checks if element has addons
         *
         * @returns {Boolean}
         */
        hasAddons: function () {
            this.fieldDepend(this.value());

            return this._super();
        },

        /**
         * Update field dependency
         *
         * @param {String} value
         */
        fieldDepend: function (value) {
            let fields = {
                '1': ['recipient_country', 'recipient_street', 'recipient_city', 'recipient_postcode'],
                '2': ['recipient_pickup']
            };

            $.each(fields, function (type, attributes) {
                $.each(attributes, function (key, attribute) {
                    let field = uiRegistry.get('index = ' + attribute);

                    if (field) {
                        field.visible(false);
                        if (value === type) {
                            field.visible(true);
                        }
                    }
                });
            });
        }
    });
});
