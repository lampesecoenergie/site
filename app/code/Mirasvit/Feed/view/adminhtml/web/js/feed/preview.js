define([
    'jquery',
    'jquery/ui',
    'Magento_Ui/js/modal/modal',
    'jquery/jquery.cookie'
], function ($, ui, modal) {
    'use strict';

    $.widget('mirasvit.feedPreview', {
        options: {
            url: null,
        },

        _create: function () {
            this.element
                .off('click.button')
                .on('click.button', $.proxy(this.preview, this));

            this._super();
        },

        preview: function () {
            var self = this;

            var modal = $('<div/>').modal({
                type: 'slide',
                title: $.mage.__('Feed Preview <sup>10 products</sup>'),
                modalClass: 'preview-aside',
                closeOnEscape: true,
                opened: function () {
                    $('body').trigger('processStart');

                    $(this).html(self.getIframe());

                    self.getForm().submit();

                    $('iframe', this).load(function () {
                        $('body').trigger('processStop');
                    });
                },
                closed: function () {
                    $('.preview-aside').remove();
                },

                buttons: [
                    {
                        text: $.mage.__('Open in new window'),
                        click: function (e) {
                            var win = window.open(self.options.url, '_blank');
                            win.focus();
                            modal.modal('closeModal');
                        }
                    },
                    {
                        text: $.mage.__('Reload'),
                        click: function (e) {
                            self.getForm().submit();
                        }
                    }
                ]
            });

            modal.modal('openModal');
        },

        getIframe: function () {
            return $('<iframe>')
                .attr('name', 'preview_iframe');
        },

        getForm: function () {
            $("[target=preview_iframe]").remove();
            var previewIds = $.cookie("feed_preview_ids");

            var $form = $('<form/>')
                .attr('action', this.options.url)
                .attr('method', 'post')
                .attr('target', 'preview_iframe')
                .css('display', 'none');

            $form.append($('<textarea>')
                .attr('name', 'data')
                .text($('#edit_form').serialize()));

            $form.append($('<input>')
                .attr('name', 'preview_ids')
                .val(previewIds));

            $('body').append($form);

            return $form;
        }
    });

    return $.mirasvit.feedPreview;
});
