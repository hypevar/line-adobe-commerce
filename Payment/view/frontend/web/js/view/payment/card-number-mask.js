/**
 * Copyright © 2026 Line. All rights reserved.
 */
define(['jquery', 'ko'], function ($, ko) {
    'use strict';

    // Register custom validator: counts only digits, ignoring formatting spaces
    $.validator.addMethod(
        'validate-cc-number-length',
        function (value) {
            var digits = (value || '').replace(/\D/g, '');

            return digits.length >= 15 && digits.length <= 20;
        },
        $.mage.__('Please enter between 15 and 20 digits.')
    );

    /**
     * @param {number} len
     * @returns {number[]|null}
     */
    function getPattern(len) {
        switch (len) {
            case 15: return [4, 7, 4];
            case 16: return [4, 4, 4, 4];
            case 18: return [10, 5, 3];
            case 20: return [4, 4, 4, 4, 4];
            default: return null;
        }
    }

    /**
     * @param {string} digits
     * @returns {string}
     */
    function formatDigits(digits) {
        if (!digits) return '';

        var pattern = getPattern(digits.length);

        if (!pattern) {
            // Progressive display: groups of 4 while typing
            return digits.replace(/(\d{4})(?=\d)/g, '$1 ');
        }

        var result = [],
            pos = 0,
            i, chunk;

        for (i = 0; i < pattern.length; i++) {
            chunk = digits.substring(pos, pos + pattern[i]);
            if (chunk.length) {
                result.push(chunk);
            }
            pos += pattern[i];
        }

        return result.join(' ');
    }

    ko.bindingHandlers.cardNumberFormat = {
        init: function (element) {
            ko.utils.registerEventHandler(element, 'input', function () {
                var oldFormatted       = element.value,
                    cursorPos          = element.selectionStart,
                    digitsBeforeCursor = Math.min(
                        oldFormatted.substring(0, cursorPos).replace(/\D/g, '').length,
                        20
                    ),
                    rawDigits          = oldFormatted.replace(/\D/g, '').substring(0, 20),
                    newFormatted       = formatDigits(rawDigits),
                    newCursorPos       = 0,
                    counted            = 0,
                    i;

                element.value = newFormatted;

                for (i = 0; i < newFormatted.length; i++) {
                    if (newFormatted[i] !== ' ') {
                        counted++;
                    }
                    if (counted === digitsBeforeCursor) {
                        newCursorPos = i + 1;
                        break;
                    }
                }

                if (digitsBeforeCursor === 0) {
                    newCursorPos = 0;
                }

                try {
                    element.setSelectionRange(newCursorPos, newCursorPos);
                } catch (e) {  }
            });
        },

        update: function (element, valueAccessor) {
            var raw       = (ko.unwrap(valueAccessor()) || '').replace(/\D/g, '').substring(0, 20),
                formatted = formatDigits(raw);

            if (element.value !== formatted) {
                element.value = formatted;
            }
        }
    };
});
