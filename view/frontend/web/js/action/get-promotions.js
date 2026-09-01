/**
 * Copyright © 2024 Line. All rights reserved.
 */

define([
    'mage/storage'
], function (storage) {
    'use strict';

    /**
     * Requests the promotions available for a card brand. Errors are reported by the caller.
     *
     * @param {Object} config
     * @param {String} cardBrand
     * @param {*} isGlobal
     * @return {Deferred}
     */
    let action = function (config, cardBrand, isGlobal) {
        return storage.post(
            config.getPromotionsActionUrl(),
            JSON.stringify({value: cardBrand}),
            isGlobal
        );
    };

    return action;
});
