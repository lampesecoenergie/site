/*global define*/
define([
    'jquery',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Customer/js/model/customer',
    'mage/storage'
], function ($, urlBuilder, customer, storage) {
    'use strict';

    return {
        getUrlForRetrievePickupAddress: function (pickupId, countryId) {
            return urlBuilder.createUrl(
                '/mondialrelayPickup/:pickupId/:countryId',
                {'pickupId':pickupId, 'countryId':countryId}
            );
        },

        getUrlForSavePickup: function (quoteId, pickupId, countryId, code) {
            var url = urlBuilder.createUrl(
                '/carts/mine/mondialrelay-pickup/:pickupId/:countryId/:code',
                {'pickupId':pickupId, 'countryId':countryId, 'code':code}
            );

            if (this.isGuest()) {
                url = urlBuilder.createUrl(
                    '/guest-carts/:cartId/mondialrelay-pickup/:pickupId/:countryId/:code',
                    {'cartId':quoteId, 'pickupId':pickupId, 'countryId':countryId, 'code':code}
                );
            }
            return url;
        },

        getUrlForCurrentPickup: function (quoteId) {
            var url = urlBuilder.createUrl('/carts/mine/mondialrelay-pickup', {});

            if (this.isGuest()) {
                url = urlBuilder.createUrl('/guest-carts/:cartId/mondialrelay-pickup', {'cartId':quoteId});
            }
            return url;
        },

        getUrlForResetPickup: function (quoteId) {
            var url = urlBuilder.createUrl('/carts/mine/mondialrelay-pickup', {});

            if (this.isGuest()) {
                url = urlBuilder.createUrl('/guest-carts/:cartId/mondialrelay-pickup', {'cartId':quoteId});
            }
            return url;
        },

        getPickup: function (pickupId, countryId) {
            return storage.get(this.getUrlForRetrievePickupAddress(pickupId, countryId), false);
        },

        currentPickup: function (quoteId) {
            return storage.get(this.getUrlForCurrentPickup(quoteId), false);
        },

        savePickup: function (quoteId, pickupId, countryId, code) {
            return storage.put(this.getUrlForSavePickup(quoteId, pickupId, countryId, code), false);
        },

        resetPickup: function (quoteId) {
            return storage.delete(this.getUrlForResetPickup(quoteId), false);
        },

        isGuest: function () {
            return !customer.isLoggedIn();
        }
    }
});