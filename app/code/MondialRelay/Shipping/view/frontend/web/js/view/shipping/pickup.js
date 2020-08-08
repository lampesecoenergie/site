/*global define*/
define([
    'jquery',
    'uiComponent',
    'ko',
    'MondialRelay_Shipping/js/lib/popup',
    'MondialRelay_Shipping/js/model/shipping/pickup',
    'MondialRelay_Shipping/js/view/checkout/address',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/quote',
    'mage/translate'
], function (
    $,
    Component,
    ko,
    popup,
    pickupModel,
    pickupAddress,
    stepNavigator,
    setShippingInformationAction,
    quote
) {
    'use strict';

    return Component.extend({
        actions:{
            'load':window.checkoutConfig.mondialrelayUrl + 'pickup/load'
        },

        initialize: function () {
            this._super();
        },

        /**
         * Run
         */
        run: function () {
            popup.open(920, 595);
            this.pickupAction();
        },

        /**
         * Load pop-up content with Ajax Request
         *
         * @param {string} action
         * @param {Object} data
         */
        loadContent: function (action, data) {
            popup.closeMessage();
            $.ajax({
                url: action,
                type: 'post',
                context: this,
                data: data,
                beforeSend: popup.loader($.mage.__('Loading...')),
                success: function (response) {
                    popup.update(response);
                }
            });
        },

        /**
         * Launch pickup action
         */
        pickupAction: function () {
            var address = quote.shippingAddress();
            var data = {};
            if (address) {
                if (address.postcode) {
                    data.postcode = address.postcode;
                }
                if (address.countryId && address.countryId !== 'US') {
                    data.country_id = address.countryId;
                }
            }

            this.loadContent(this.actions.load, data);
        },

        /**
         * Init Pickup action
         *
         * @param {Object.<number, Object>} locations
         * @param {Object} maps
         */
        pickupInit: function (locations, maps) {
            var pickup = this;

            /* Form Pickup */
            $('#mr-pickup').submit(function (event) {
                var checked = $(this).find("input[name=pickup]:checked");

                if (checked.length) {
                    popup.loader($.mage.__('Loading...'));
                    var pickupData = checked.val().split('-');

                    pickup.pickupUpdateQuote(pickupData[0], pickupData[1], pickupData[2]);
                } else {
                    popup.error($.mage.__('Please select pickup'));
                }
                event.preventDefault();
            });

            /* Form Address */
            $('#mr-address').submit(function (event) {
                pickup.loadContent(pickup.actions.load, $(this).serializeArray());
                event.preventDefault();
            });

            /* Form pickup code */
            $('#mr-address-code').find('input').click(function () {
                $('#mr-address-code').find('label').removeClass('active');
                $(this).next('label').addClass('active');
            });

            /* Back button */
            $('#mr-previous').click(function (event) {
                popup.close();
                event.preventDefault();
            });

            /* Select pickup */
            $('#mr-list').find('input').click(function () {
                $('#mr-list').find('li').removeClass('active');
                $(this).parent('li').addClass('active');
                maps.update($(this).attr('id'));
            });

            /* Show info */
            $('.mondialrelay-show-info').click(function (event) {
                popup.message($(this).parent('label').next('div').html(), false);
                $(popup.PopupMessage).find('button').click(function () {
                    popup.closeMessageWithEffect();
                });
                event.preventDefault();
            });

            /* Debug */
            $('#mr-debug').click(function (event) {
                popup.message($('#mr-debug-content').html(), false);
                $(popup.PopupMessage).find('button').click(function () {
                    popup.closeMessageWithEffect();
                });
                event.preventDefault();
            });

            maps.run('mr-map', 'mr-list');
            maps.locations(locations);
        },

        pickupUpdateQuote: function (pickupId, countryId, code) {
            var pickup = this;

            if (typeof pickupId === 'undefined') {
                pickupId = null;
            }

            if (typeof countryId === 'undefined') {
                countryId = null;
            }

            if (typeof code === 'undefined') {
                code = null;
            }

            if (pickupId && countryId) {
                var address = pickupModel.getPickup(pickupId, countryId);
                address.done(
                    function (data) {
                        var save = pickupModel.savePickup(quote.getQuoteId(), pickupId, countryId, code);
                        save.done(
                            function () {
                                pickupAddress.pickupAddress(data);
                                pickup.pickupUpdateAddress();
                                popup.close();
                                if (window.checkoutConfig.mondialrelayOpen === '0' && stepNavigator.getActiveItemIndex() === 0) {
                                    stepNavigator.next();
                                }
                            }
                        ).fail(
                            function () {
                                pickupAddress.pickupAddress('');
                                popup.error($.mage.__('Unable to load pickup now, please select another shipping method'));
                            }
                        );
                    }
                ).fail(
                    function () {
                        pickupAddress.pickupAddress('');
                        popup.error($.mage.__('Unable to load pickup now, please select another shipping method'));
                    }
                );
            }
        },

        pickupUpdateAddress: function () {
            var pickup = this;

            var label = $('#label_method_pickup_mondialrelay');

            if (label.length) {
                if (!$('#mondialrelay_pickup_address').length) {
                    label.parent('tr').after(
                        '<tr id="mondialrelay_pickup_address">' +
                            '<td id="mondialrelay_pickup_address_content" colspan="4"></td>' +
                        '</tr>'
                    );

                    /* Compatibility with Aheadworks_OneStepCheckout */
                    label.next('.shipping-method-subtitle').append(
                        '<div id="mondialrelay_pickup_address">' +
                            '<span id="mondialrelay_pickup_address_content"></span>' +
                        '</div>'
                    );
                }

                ko.utils.setHtml(
                    $('#mondialrelay_pickup_address_content'),
                    $('#mondialrelay-pickup-selected').html()
                );

                $('.mr-update-pickup').click(function (event) {
                    pickup.run();
                    event.preventDefault();
                });
            }
        },

        pickupRemoveAddress: function (resetPickup) {
            if ($('#mondialrelay_pickup_address').length) {
                $('#mondialrelay_pickup_address').remove();
            }
            pickupAddress.pickupAddress('');
            if (resetPickup) {
                pickupModel.resetPickup(quote.getQuoteId());
            }
        }
    });
});