/**
 * Copyright © 2024 Line. All rights reserved.
 */

define([
    'jquery',
    'mage/storage',
    'Magento_Ui/js/model/messageList',
    'mage/translate',
    'mage/cookies'
], function ($, storage, globalMessageList, $t) {
    'use strict';

    var ERROR_MESSAGE = $t('Cannot reach backend to retrieve promotions, please contact your administrator.'),

    /**
     * @param {Object} config
     * @param {*} isGlobal
     * @param {Object} messageContainer
     */
   action = function (config, bin, isGlobal, messageContainer) {
        messageContainer = messageContainer || globalMessageList;

        // The controller validates the form key. mage/storage posts a JSON document, so the key
        // cannot travel as a request parameter and goes in the body instead.
        return storage.post(
            config.getPromotionsByBinActionUrl(),
            JSON.stringify({
                value: bin,
                form_key: $.mage.cookies.get('form_key')
            }),
            isGlobal
        ).done(function (response) {
            if (response.errors) {
                messageContainer.addErrorMessage(response.message);
                return response;
            } else {
                return response.result;
            }
        }).fail(function () {
            messageContainer.addErrorMessage({
                'message': ERROR_MESSAGE
            });
        })
    };

    return action;
});
