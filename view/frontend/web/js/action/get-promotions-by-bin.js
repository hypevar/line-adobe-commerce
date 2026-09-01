/**
 * Copyright © 2024 Line. All rights reserved.
 */

define([
    'jquery',
    'mage/storage',
    'mage/cookies'
], function ($, storage) {
    'use strict';

    /**
     * Requests the promotions available for a card BIN. Errors are reported by the caller.
     * The form key travels in the body because mage/storage posts a JSON document.
     *
     * @param {Object} config
     * @param {String} bin
     * @param {*} isGlobal
     * @return {Deferred}
     */
    let action = function (config, bin, isGlobal) {
        return storage.post(
            config.getPromotionsByBinActionUrl(),
            JSON.stringify({
                value: bin,
                form_key: $.mage.cookies.get('form_key')
            }),
            isGlobal
        );
    };

    return action;
});
