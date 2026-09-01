/**
 *
 *
 */

/* @api */
define([
    'jquery',
    'mageUtils'
], function ($, utils) {
    'use strict';

    var types = [];

    return {
        setTypes: function (list) {
            types = list;
        },

        /**
         * @param {*} cardNumber
         * @return {Array}
         */
        getCardTypes: function (cardNumber) {
            var i, value,
                result = [];

            if (utils.isEmpty(cardNumber)) {
                return result;
            }

            if (cardNumber === '') {
                return $.extend(true, {}, types);
            }

            for (i = 0; i < types.length; i++) {
                value = types[i];

                if (new RegExp(value.pattern).test(cardNumber)) {
                    result.push($.extend(true, {}, value));
                }
            }

            return result;
        }
    };
});
