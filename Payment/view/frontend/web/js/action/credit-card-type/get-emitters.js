/**
 * Copyright © 2024 Line. All rights reserved.
 */

define([
    'jquery',
    'mage/storage',
    'Magento_Ui/js/model/messageList',
    'mage/translate'
], function ($, storage, globalMessageList, $t) {
    'use strict';

    /**
     * @param {Object} config
     * @param {*} isGlobal
     * @param {Object} messageContainer
     */
    var action = function (config, isGlobal, messageContainer) {
        messageContainer = messageContainer || globalMessageList;
        let serviceUrl = config.getEmittersActionUrl() || '';

        return storage.get(
            serviceUrl,
            isGlobal
        ).done(function (response) {
            if (response.errors) {
                messageContainer.addErrorMessage(response.message);
            } else {
                return response.result;
            }
        }).fail(function () {
            messageContainer.addErrorMessage({
                'message': $t('Could not retrieve emitters. Please try again later')
            });
        });
    };

    return action;
});
