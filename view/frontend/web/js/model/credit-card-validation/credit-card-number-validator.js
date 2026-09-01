/**
 *
 *
 */

/* @api */
define([
    'mageUtils',
    'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator/luhn10-validator',
    'Line_Payment/js/model/credit-card-validation/credit-card-number-validator/credit-card-type'
], function (
    utils,
    luhn10,
    creditCardTypes
) {
    'use strict';

    /**
     * @param {*} card
     * @param {*} isPotentiallyValid
     * @param {*} isValid
     * @return {Object}
     */
    function resultWrapper(card, isPotentiallyValid, isValid) {
        return {
            card: card,
            isValid: isValid,
            isPotentiallyValid: isPotentiallyValid
        };
    }

    return function (emitters, value) {
        var potentialTypes,
            cardType,
            valid,
            i,
            maxLength;

        if (utils.isEmpty(value)) {
            return resultWrapper(null, false, false);
        }

        creditCardTypes.setTypes(emitters);

        value = value.replace(/\s+/g, '');

        if (!/^\d*$/.test(value)) {
            return resultWrapper(null, false, false);
        }

        potentialTypes = creditCardTypes.getCardTypes(value);

        if (potentialTypes.length === 0) {
            return resultWrapper(null, false, false);
        }

        // force returning first match
        cardType = potentialTypes[0];

        // UnionPay is not Luhn 10 compliant
        if (!cardType.validateLuhn) {
            valid = true;
        } else {
            valid = luhn10(value);
        }

        for (i = 0; i < cardType.lengths.length; i++) {
            if (cardType.lengths[i] === value.length) {
                return resultWrapper(cardType, valid, valid);
            }
        }

        maxLength = Math.max.apply(null, cardType.lengths);

        if (value.length < maxLength) {
            return resultWrapper(cardType, true, false);
        }

        return resultWrapper(cardType, false, false);
    };
});
