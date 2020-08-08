/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/grid/data-storage'
], function (Storage) {
    'use strict';

    return Storage.extend({
        defaults: {
            cacheRequests: false
        }
    });
});
