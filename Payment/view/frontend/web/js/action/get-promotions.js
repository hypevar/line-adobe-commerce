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

    var ERROR_MESSAGE = $t('Cannot reach backend to retrieve promotions, please contact your administrator.'),

    /**
     * @param {Object} config
     * @param {*} isGlobal
     * @param {Object} messageContainer
     */
   action = function (config, cardBrand, isGlobal, messageContainer) {
        messageContainer = messageContainer || globalMessageList;

        return storage.post(
            config.getPromotionsActionUrl(),
            JSON.stringify({value: cardBrand}),
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
