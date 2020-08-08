/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(
    [
        'jquery',
        'underscore',
        'mageUtils',
        'uiRegistry',
        'Magento_Ui/js/grid/columns/actions',
        'Magento_Ui/js/modal/confirm',
        'Magento_Ui/js/lib/spinner',
        'Ced_Integrator/js/modal/popup',
        'vkbeautify',
        'highlight',
        'j2t'
    ], function ($, _, utils, registry, Column, confirm, loader, popup, vk, highlight, j2t) {
        'use strict';

        return Column.extend(
            {
                defaults: {
                    bodyTmpl: 'Ced_Integrator/grid/cells/actions',
                    sortable: false,
                    draggable: false,
                    actions: [],
                    rows: [],
                    rowsProvider: '${ $.parentName }',
                    fieldClass: {
                        'data-grid-actions-cell': true
                    },
                    templates: {
                        actions: {}
                    },
                    imports: {
                        rows: '${ $.rowsProvider }:rows'
                    },
                    listens: {
                        rows: 'updateActions'
                    }
                },

                /**
                 * Initializes observable properties.
                 *
                 * @returns {ActionsColumn} Chainable.
                 */
                initObservable: function () {
                    this._super()
                        .track('actions');

                    return this;
                },

                /**
                 * Returns specific action of a specified row
                 * or all action objects associated with it.
                 *
                 * @param   {Number} rowIndex - Index of a row.
                 * @param   {String} [actionIndex] - Action identifier.
                 * @returns {Array|Object}
                 */
                getAction: function (rowIndex, actionIndex) {
                    var rowActions = this.actions[rowIndex];

                    return rowActions && actionIndex ?
                        rowActions[actionIndex] :
                        rowActions;
                },

                /**
                 * Returns visible actions for a specified row.
                 *
                 * @param   {Number} rowIndex - Index of a row.
                 * @returns {Array} Visible actions.
                 */
                getVisibleActions: function (rowIndex) {
                    var rowActions = this.getAction(rowIndex);

                    return _.filter(rowActions, this.isActionVisible, this);
                },

                /**
                 * Adds new action. If action with a specfied identifier
                 * already exists, than the original will be overrided.
                 *
                 * @param   {String} index - Actions' identifier.
                 * @param   {Object} action - Actions' data.
                 * @returns {ActionsColumn} Chainable.
                 */
                addAction: function (index, action) {
                    var actionTmpls = this.templates.actions;

                    actionTmpls[index] = action;

                    this.updateActions();

                    return this;
                },

                /**
                 * Recreates actions for each row.
                 *
                 * @returns {ActionsColumn} Chainable.
                 */
                updateActions: function () {
                    this.actions = this.rows.map(this._formatActions, this);

                    return this;
                },

                /**
                 * Processes actions, setting additional information to them and
                 * evaluating ther properties as a string templates.
                 *
                 * @private
                 * @param   {Object} row - Row object.
                 * @param   {Number} rowIndex - Index of a row.
                 * @returns {Array}
                 */
                _formatActions: function (row, rowIndex) {
                    var rowActions = row[this.index] || {},
                        recordId = row[this.indexField],
                        customActions = this.templates.actions;

                    /**
                     * Actions iterator.
                     */
                    function iterate(action, index) {
                        action = utils.extend(
                            {
                                index: index,
                                rowIndex: rowIndex,
                                recordId: recordId
                            }, action
                        );

                        return utils.template(action, row, true);
                    }

                    rowActions = _.mapObject(rowActions, iterate);
                    customActions = _.map(customActions, iterate);

                    customActions.forEach(
                        function (action) {
                            rowActions[action.index] = action;
                        }
                    );

                    return rowActions;
                },

                /**
                 * Applies specified action.
                 *
                 * @param   {String} actionIndex - Actions' identifier.
                 * @param   {Number} rowIndex - Index of a row.
                 * @returns {ActionsColumn} Chainable.
                 */
                applyAction: function (actionIndex, rowIndex) {
                    var action = this.getAction(rowIndex, actionIndex),
                        callback = this._getCallback(action);

                    if (action.confirm) {
                        this._confirm(action, callback);
                    } else if (action.popup) {
                        this._popup(action, callback);
                    } else if (action.disable) {
                        //Do nothing
                    } else {
                        callback();
                    }

                    return this;
                },

                /**
                 * Shows modal window.
                 *
                 * @TODO  implement loader, optimize performance
                 * @param {Object} action - Actions' data.
                 * @param {Function} callback - Callback that will be
                 *      invoked if action is confirmed.
                 */
                _popup: function (action, callback) {
                    var data = action.popup;
                    var dataType = data.type === undefined || data.type === '' ? 'xml' : data.type;
                    var download = false;

                    //Start loader
                    // var body = $('body').loader();
                    // body.loader('show');
                    this.showLoader();

                    if (data.file !== undefined && data.file !== '') {
                        $.ajax(
                            {
                                url: data.file,
                                async: false,
                                dataType: "text",
                                type: 'GET',
                                //showLoader: true, //use for display loader
                                success: function (response, status, request) {
                                    var size = request.responseText.length / 1024;

                                    // Skipping files larger than 500KB
                                    if (size < 500) {
                                        if (dataType === 'xml') {
                                            response = _.escape(vk.xml(response));
                                        } else if (dataType === 'json' && data.render !== 'html') {
                                            response = vk.json(response);
                                        }
                                    } else {
                                        download = true;
                                    }

                                    data.message = response;
                                }
                            }
                        );
                    }

                    if (download === false) {
                        var content = '<div style="max-height: 450px; overflow: auto;"><pre><code>' + data.message + '</code></pre></div>';
                        if (data.render === 'html' && dataType === 'json') {
                            var result = this.tryParseJSON(data.message);
                            if (result && Object.keys(result).length > 0) {
                                result = j2t.convert(result);
                            }
                            content = '<div style="max-height: 450px; overflow: auto;">' + result + '</div>';
                        }

                        popup(
                            {
                                title: data.title,
                                content: content,
                                actions: {
                                    confirm: callback
                                }
                            }
                        );

                        $(document).ready(
                            function () {
                                $('pre code').each(
                                    function (i, block) {
                                        var data = highlight.highlightBlock(block);
                                    }
                                );
                            }
                        );
                    } else {
                        var name = data.file.substring(data.file.lastIndexOf('/')+1);
                        var a = document.createElement('a');
                        a.href = window.URL.createObjectURL(new Blob([data.message], {type: "text/plain;charset=utf-8"}))
                        a.download = name;
                        a.style.display = 'none';
                        document.body.appendChild(a);
                        a.click();
                    }

                    //Stop loader
                    //body.loader('hide');
                    this.hideLoader();
                },

                /**
                 * Hides loader.
                 */
                hideLoader: function () {
                    //TODO: enable loader
                    //loader.get('amazon_product_listing.amazon_product_listing.product_columns').hide();
                },

                /**
                 * Shows loader.
                 */
                showLoader: function () {
                   //loader.get('amazon_product_listing.amazon_product_listing.product_columns').show();
                },

                tryParseJSON: function (jsonString) {
                    try {
                        var o = JSON.parse(jsonString);

                        // Handle non-exception-throwing cases:
                        // Neither JSON.parse(false) or JSON.parse(1234) throw errors, hence the type-checking,
                        // but... JSON.parse(null) returns null, and typeof null === "object",
                        if (o && typeof o === "object") {
                            return o;
                        }
                    } catch (e) {
                    }

                    return false;
                },

                /**
                 * Creates handler for the provided action if it's required.
                 *
                 * @param   {Object} action - Action object.
                 * @returns {Function|Undefined}
                 */
                getActionHandler: function (action) {
                    var index = action.index,
                        rowIndex = action.rowIndex;

                    if (this.isHandlerRequired(index, rowIndex)) {
                        return this.applyAction.bind(this, index, rowIndex);
                    }
                },

                /**
                 * Checks if specified action requires a handler function.
                 *
                 * @param   {String} actionIndex - Actions' identifier.
                 * @param   {Number} rowIndex - Index of a row.
                 * @returns {Boolean}
                 */
                isHandlerRequired: function (actionIndex, rowIndex) {
                    var action = this.getAction(rowIndex, actionIndex);

                    return _.isObject(action.callback) || action.confirm || !action.href;
                },

                /**
                 * Creates action callback based on its' data. If action doesn't spicify
                 * a callback function than the default one will be used.
                 *
                 * @private
                 * @param   {Object} action - Actions' object.
                 * @returns {Function} Callback function.
                 */
                _getCallback: function (action) {
                    var args = [action.index, action.recordId, action],
                        callback = action.callback;

                    if (utils.isObject(callback)) {
                        args.unshift(callback.target);

                        callback = registry.async(callback.provider);
                    } else if (_.isArray(callback)) {
                        return this._getCallbacks(action);
                    } else if (!_.isFunction(callback)) {
                        callback = this.defaultCallback.bind(this);
                    }

                    return function () {
                        callback.apply(callback, args);
                    };
                },

                /**
                 * Creates action callback for multiple actions.
                 *
                 * @private
                 * @param   {Object} action - Actions' object.
                 * @returns {Function} Callback function.
                 */
                _getCallbacks: function (action) {
                    var callback = action.callback,
                        callbacks = [],
                        tmpCallback;

                    _.each(
                        callback, function (cb) {
                            tmpCallback = {
                                action: registry.async(cb.provider),
                                args: _.compact([cb.target, cb.params])
                            };
                            callbacks.push(tmpCallback);
                        }
                    );

                    return function () {
                        _.each(
                            callbacks, function (cb) {
                                cb.action.apply(cb.action, cb.args);
                            }
                        );
                    };
                },

                /**
                 * Default action callback. Redirects to
                 * the specified in actions' data url.
                 *
                 * @param {String} actionIndex - Actions' identifier.
                 * @param {(Number|String)} recordId - Id of the record accociated
                 *      with a specfied action.
                 * @param {Object} action - Actions' data.
                 */
                defaultCallback: function (actionIndex, recordId, action) {
                    window.location.href = action.href;
                },

                /**
                 * Shows actions' confirmation window.
                 *
                 * @param {Object} action - Actions' data.
                 * @param {Function} callback - Callback that will be
                 *      invoked if action is confirmed.
                 */
                _confirm: function (action, callback) {
                    var confirmData = action.confirm;

                    confirm(
                        {
                            title: confirmData.title,
                            content: confirmData.message,
                            actions: {
                                confirm: callback
                            }
                        }
                    );
                },

                /**
                 * Checks if row has only one visible action.
                 *
                 * @param   {Number} rowIndex - Row index.
                 * @returns {Boolean}
                 */
                isSingle: function (rowIndex) {
                    return this.getVisibleActions(rowIndex).length === 1;
                },

                /**
                 * Checks if row has more than one visible action.
                 *
                 * @param   {Number} rowIndex - Row index.
                 * @returns {Boolean}
                 */
                isMultiple: function (rowIndex) {
                    return this.getVisibleActions(rowIndex).length > 1;
                },

                /**
                 * Checks if action should be displayed.
                 *
                 * @param   {Object} action - Action object.
                 * @returns {Boolean}
                 */
                isActionVisible: function (action) {
                    return action.hidden !== true;
                },

                /**
                 * Overrides base method, because this component
                 * can't have global field action.
                 *
                 * @returns {Boolean} False.
                 */
                hasFieldAction: function () {
                    return false;
                },


                // This function creates a standard table with column/rows
                // Parameter Information
                // objArray = Anytype of object array, like JSON results
                // theme (optional) = A css class to add to the table (e.g. <table class="<theme>">
                // enableHeader (optional) = Controls if you want to hide/show, default is show
                CreateTableView: function (objArray, theme, enableHeader) {
                    // set optional theme parameter
                    if (theme === undefined) {
                        theme = {
                            'table': 'data-grid',
                            'td': '',
                            'th': 'data-grid-th',
                            'tr': 'data-row'
                        }; //default
                    }

                    if (enableHeader === undefined) {
                        enableHeader = true; //default enable headers
                    }

                    if (typeof objArray === 'function') {
                        return "";
                    }
                    if (typeof objArray === 'string') {
                        return objArray;
                    }

                    // If the returned data is an object do nothing, else try to parse
                    var array = typeof objArray != 'object' ? JSON.parse(objArray) : new Array(objArray);
                    var keys = Object.keys(array[0]);

                    var str = '<table class="' + theme.table + '">';

                    // table head
                    if (enableHeader) {
                        str += '<thead><tr class="' + theme.tr + '">';
                        for (var index in keys) {
                            if ($.isNumeric(index)) {
                                str += '<th scope="col" class="' + theme.th + '">' + keys[index] + '</th>';
                            }
                        }
                        str += '</tr></thead>';
                    }

                    // table body
                    str += '<tbody>';
                    for (var i = 0; i < array.length; i++) {
                        str += (i % 2 == 0) ? '<tr class="alt" class="' + theme.tr + '">' : '<tr>';
                        for (var index in keys) {
                            if (keys.hasOwnProperty(index)) {
                                var objValue = array[i][keys[index]];

                                // Support for Nested Tables
                                if (typeof objValue === 'object' && objValue !== null) {
                                    if (Array.isArray(objValue)) {
                                        str += '<td class="' + theme.td + '">';
                                        for (var aindex in objValue) {
                                            str += this.CreateTableView(objValue[aindex], theme, true);
                                        }
                                        str += '</td>';
                                    } else {
                                        str += '<td class="' + theme.td + '">' + this.CreateTableView(objValue, theme, true) + '</td>';
                                    }
                                } else {
                                    str += '<td class="' + theme.td + '">' + objValue + '</td>';
                                }
                            }
                        }
                        str += '</tr>';
                    }
                    str += '</tbody>';
                    str += '</table>';

                    return str;
                },

                // This function creates a details view table with column 1 as the header and column 2 as the details
                // Parameter Information
                // objArray = Anytype of object array, like JSON results
                // theme (optional) = A css class to add to the table (e.g. <table class="<theme>">
                // enableHeader (optional) = Controls if you want to hide/show, default is show
                CreateDetailView: function (objArray, theme, enableHeader) {
                    // set optional theme parameter
                    if (theme === undefined) {
                        theme = {
                            'table': 'data-grid',
                            'td': '',
                            'th': 'data-grid-th',
                            'tr': 'data-row'
                        }; //default
                    }

                    if (enableHeader === undefined) {
                        enableHeader = true; //default enable headers
                    }

                    // If the returned data is an object do nothing, else try to parse
                    var array = typeof objArray != 'object' ? JSON.parse(objArray) : new Array(objArray);
                    var keys = Object.keys(array[0]);

                    var str = '<table class="' + theme.table + '">';
                    str += '<tbody>';


                    for (var i = 0; i < array.length; i++) {
                        var row = 0;
                        for (var index in keys) {
                            var objValue = array[i][keys[index]];

                            str += (row % 2 == 0) ? '<tr class="alt">' : '<tr>';

                            if (enableHeader) {
                                str += '<th scope="row" class="' + theme.th + '">' + keys[index] + '</th>';
                            }

                            // Support for Nested Tables
                            if (typeof objValue === 'object' && objValue !== null) {
                                if (Array.isArray(objValue)) {
                                    str += '<td class="' + theme.td + '">';
                                    for (var aindex in objValue) {
                                        str += this.CreateDetailView(objValue[aindex], theme, true);
                                    }
                                    str += '</td>';
                                } else {
                                    str += '<td class="' + theme.td + '">' + this.CreateDetailView(objValue, theme, true) + '</td>';
                                }
                            } else {
                                str += '<td class="' + theme.td + '">' + objValue + '</td>';
                            }

                            str += '</tr>';
                            row++;
                        }
                    }
                    str += '</tbody>';
                    str += '</table>';
                    return str;
                }

            }
        );
    }
);
