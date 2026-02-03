/**
 * Copyright © 2023 Line. All rights reserved.
 */

/**
 *
 */
define([], function () {
    'use strict';

    var config = window.checkoutConfig.payment.linepayment;

    return {
        getUrl: function() {
            return config.env.url;
        },

        getPublicKey: function() {
            return config.env.public_key;
        },

        isSandboxEnabled: function () {
            return config.env.is_sandbox
        },

        getCode: function () {
            return config.code
        },

        getIsActive: function() {
            return config.is_active;
        },

        getCcAvailableTypes: function () {
            return config.available_types;
        },

        getCcMonths: function () {
            return config.months;
        },

        getCcYears: function () {
            return config.years;
        },

        getDocumentTypes: function () {
            return config.document_types;
        },

        hasVerification: function () {
            return config.has_verification;
        },

        getCvvImageUrl: function () {
            return config.cvv_image_url;
        },

        isShowLegend: function () {
            return true;
        },

        getIcons: function (type) {
            return config.icons.hasOwnProperty(type)
                ? config.icons[type]
                : false;
        },

        getPromotionsByBinActionUrl: function () {
            return config.promotions_by_bin_action_url;
        },

        getPromotionsActionUrl: function () {
            return config.promotions_action_url;
        },

        // getEmitters: function () {
        //     return config.emitters.result;
        // },

        /**
         * Whether to display install the price of each installment
         * within the installemnts dropdown
         *
         * @returns {Boolean}
         */
        displayInstallmentPrice: function () {
            return config.installments.displayPrice;
        },

        isInstallmentsFilterEnabled: function () {
            return config.installments.filter.enabled;
        },

        getInstallmentsFilterConfiguration: function () {
            return config.installments.filter.config;
        },

        getCcMethods: function () {
            return config.credit_card_method;
        }
    }
});
