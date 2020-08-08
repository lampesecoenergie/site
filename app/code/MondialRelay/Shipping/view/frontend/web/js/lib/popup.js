/*global define*/
define([
    'jquery'
], function (
    $
) {
    'use strict';
    return {
        PopupContainer: null,
        PopupInner: null,
        PopupMessage: null,
        PopupLoader: null,
        PopupIsMobile: false,

        /**
         * Open Pop-Up
         *
         * @param {number} width
         * @param {number} height
         * @returns {boolean}
         */
        open: function (width, height) {
            if (this.PopupContainer && this.PopupInner) {
                return false;
            }

            /* Create container */
            var container = document.createElement('div');
            $(container)
                .addClass('mgx-popup-container')
                .css({'height':$(window).height(),'cursor':'pointer'})
                .click($.proxy(function () {
                    this.close();
                }, this));
            this.PopupContainer = container;

            /* Create container inner */
            var inner = document.createElement('div');
            $(inner)
                .addClass('mgx-popup-inner')
                .css({'marginTop':(-height/2)+'px'});
            this.PopupInner = inner;

            /* Check device */
            var w = window,
                d = document,
                e = d.documentElement,
                g = d.getElementsByTagName('body')[0],
                x = w.innerWidth || e.clientWidth || g.clientWidth;

            if (x <= width) {
                this.PopupIsMobile = true;
                $(this.PopupInner).css({'marginTop':0,'top':0});
            }

            /* Insert Popup */
            var body = $('body');
            body.prepend(this.PopupContainer);
            body.prepend(this.PopupInner);

            return true;
        },

        /**
         * Update Pop-Up Content
         *
         * @param {string} content
         */
        update: function (content) {
            this.closeLoader();
            if (this.PopupInner) {
                $(this.PopupInner).html(content);
            }
        },

        /**
         * Close Pop-Up
         */
        close: function () {
            /* Remove container inner */
            if (this.PopupInner) {
                $(this.PopupInner).remove();
                this.PopupInner = null;
            }
            /* Remove container */
            if (this.PopupContainer) {
                $(this.PopupContainer).remove();
                this.PopupContainer = null;
            }
        },

        /**
         * Show error message
         *
         * @param {string} message
         */
        error: function (message) {
            this.message('<span class="warning">' + message + '</span>', true);
        },

        /**
         * Show message
         *
         * @param {string} message
         * @param {boolean} close
         */
        message: function (message, close) {
            this.closeLoaderWithEffect();
            this.closeMessage();

            /* Create message element */
            var element = document.createElement('div');
            $(element).addClass('mgx-message').html(message);
            this.PopupMessage = element;

            $(this.PopupInner).prepend(this.PopupMessage);

            var height = parseInt($(this.PopupMessage).height())+5;
            $(this.PopupMessage).css({'marginTop': '-' + height + 'px'});

            if (close) {
                $(this.PopupMessage).click($.proxy(function () {
                    this.closeMessageWithEffect();
                }, this));
            }

            $(this.PopupMessage).animate({'marginTop': 0}, 500);
        },

        /**
         * Close message
         */
        closeMessage: function () {
            if (this.PopupMessage) {
                $(this.PopupMessage).remove();
                this.PopupMessage = null;
            }
        },

        /**
         * Close message with animate effect
         */
        closeMessageWithEffect: function () {
            if (this.PopupMessage) {
                var height = parseInt($(this.PopupMessage).height())+5;
                $(this.PopupMessage).animate({'marginTop': '-' + height}, 500, $.proxy(function () {
                    this.closeMessage();
                }, this));
            }
        },

        /**
         * Show loader
         *
         * @param {string} content
         */
        loader: function (content) {
            this.closeLoader();

            /* Add loader element */
            var loader = document.createElement('div');
            $(loader).addClass('mgx-load').html('<span class="mgx-load-inner">' + content + '</span>');
            this.PopupLoader = loader;

            $(this.PopupInner).prepend(this.PopupLoader);

            $(this.PopupInner).find('button').each(function (item) {
                $(item).prop('disabled', true);
            });
        },

        /**
         * Close loader
         */
        closeLoader: function () {
            if (this.PopupLoader) {
                $(this.PopupLoader).remove();
                this.PopupLoader = null;
                $(this.PopupInner).find('button').each(function (item) {
                    $(item).prop('disabled', false);
                });
            }
        },

        /**
         * Close loader with fade out effect
         */
        closeLoaderWithEffect: function () {
            if (this.PopupLoader) {
                $(this.PopupLoader).fadeOut(300, function () {
                    this.closeLoader();
                });
            }
        }
    };
});
