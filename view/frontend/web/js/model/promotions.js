/**
 * Copyright © 2024 Line. All rights reserved.
 */
define([
    'ko',
    'underscore',
    'mage/translate',
    'Line_Payment/js/model/config',
    'Magento_Checkout/js/model/totals',
    'Magento_Catalog/js/price-utils',
    'domReady!'
], function (
    ko,
    _,
    $t,
    config,
    checkoutTotals,
    priceUtils
) {
    'use strict';

    var plans = ko.observable([]),
        activePlans = ko.observable([]),
        selectedPlan = ko.observable(null),
        displayInstallmentPrice = config.displayInstallmentPrice(),
        gatewayPromotions = ko.observable({}),
        paymentData = ko.observable({
            card: {
                type: '',
                brand: ''
            },
            merchant: ''
        });

    /**
     * Returns a ready object to be rendered within the Installments dropdown
     *
     * currently, `data` needs to be an object with this properties:
     *  - `displayName`: (string) to be used as the text shown in the dropdown option
     *  - `rate`: (float) coeficient/interest
     *  - `quantity`: (int) indicates the amount of installments
     *
     * @param {Object} data required information to be used to build an installment
     * @param {Boolean} displayPrice whether price-per-installment needs to be displayed in the option label
     * @param {Number} grandTotal if `displayPrice`, then we need to know the current Grand Total
     *
     * @returns {Object}
     */
    var buildPlan = function (data, displayPrice, grandTotal) {
        var display = data.displayName,
            qty = parseInt(data.quantity);

        // check if we need to display the price of each installment
        if (displayPrice) {
            var coef = parseFloat(data.rate),
                fee = 0,
                price = (grandTotal / qty);

            if (coef > 1) {
                fee = (grandTotal * coef) - grandTotal;
                price = (grandTotal + fee) / qty;
            }

            display += " (" + qty + ' x ' + priceUtils.formatPrice(price.toFixed(2)) + ")";
        }

        return {
            value: qty,
            label: display,
            rate: parseFloat(data.rate) || 1.0
        };
    };

    /**
     * Builds a 1-installment drodpown option with default values (aka. 1 installment)
     *
     * @returns {Object} a Plan option to be rendered within installment dropdown
     */
    var buildDefaultPlan = function () {
        return buildPlan({
                displayName: $t('1 Cuota'),
                rate: 1,
                quantity: 1
            },
            displayInstallmentPrice,
            checkoutTotals.totals().grand_total
        );
    };

    var stopper = function (data) {
        var total = checkoutTotals.totals().grand_total,
            isEnabled = config.isInstallmentsFilterEnabled(),
            configuration = config.getInstallmentsFilterConfiguration();

        // @TODO: refactor so `gatewayPromotions.subscribe()` sets the `activePlans()`
        // and stopper modifies that. instead of being `stopper` the one who sets it
        // stopper is responsible for filtering, not for making them available

        // if not enabled or nothing is configured, set calculated data as active
        if (!isEnabled || !configuration.length) {
            return activePlans(data);
        }

        // if no promotions came, set received data as active
        if (!gatewayPromotions().hasOwnProperty('promotions')
            || !gatewayPromotions().promotions.length
        ) {
            return activePlans(data);
        }

        var filterApplied;
        // grab all filters that apply, based on current total
        for (var i=0; i < configuration.length; i++) {
            // first match will be the winner
            if (total >= parseFloat(configuration[i].minimum)) {
                filterApplied = configuration[i];
            }
        }

        var plansFiltered = data.filter(function (installment) {
            for(var i=0; i<=filterApplied.installments.length;i++) {
                return filterApplied.installments.includes(installment.value);
            }
        });

        // If, after filtering, there's no installments left
        // we should provide at least 1 installment payment
        if (!plansFiltered.length) {
            plansFiltered = {
                'label': '1 Cuota',
                'value': 1
            };
        }

        activePlans(plansFiltered);
    };

    /**
     * Create a listener if Checkout totals gets updated after we've filtered promotions
     * If that's the case, we have to be sure we're filtering promotions correctly
     */
    checkoutTotals.totals.subscribe(function (totals) {
        if (!gatewayPromotions().hasOwnProperty('promotions') || !gatewayPromotions().promotions.length) {
            return;
        }
        stopper(plans());
    });

    /**
     * Parses Gateway response and updates available Plans (installments), Merchant and CC information
     */
    gatewayPromotions.subscribe(function (response) {

        if (!response.promotions) {
            paymentData({}); plans([]);
            return;
        }

        var promotions = response.promotions.length ? response.promotions[0] : false,
            cardBrand = response.cardBrand,
            defaultMerchant = response.defaultMerchant,
            planList = [];

        // no promotions available
        if (!promotions || !promotions.installments.length) {
            var data = {
                card: {
                    type: 'CREDITO',
                    brand: cardBrand
                },
                merchant: defaultMerchant.number
            };

            // we'll show up default one
            planList.push(buildDefaultPlan());

            // load up installment options
            plans(planList);

            // push the update to fire up cascade update
            paymentData(data);
            return;
        }

        // parse available installments
        var planList = _.map(promotions.installments, function (installment) {
            return buildPlan(
                installment,
                displayInstallmentPrice,
                checkoutTotals.totals().grand_total
            );
        });

        // sort ascending by installment amount
        planList = planList.sort(function (a, b) {
            return parseInt(a.value) - parseInt(b.value);
        });

        var data = {
            card: {
                type: promotions.bin
                    ? promotions.bin.cardType
                    : promotions.merchant
                        ? promotions.merchant.brand
                        : 'unknown',
                brand: cardBrand
            },
            merchant: promotions.merchant.number
        };

        plans(planList);
        paymentData(data);
    });

    return {
        // Holds Credit Card information
        paymentData: paymentData,
        // Contains available installments
        promotionsList: activePlans,
        // Holds the currently selected installment plan (including rate)
        selectedPlan: selectedPlan,

        /**
         * Process Gateway response for the given Credit Card number
         *
         * @param {Object} response gateway response
         */
        loadPromotions: function (response) {
            gatewayPromotions(response);
            // @TODO: refactor so `gatewayPromotions.subscribe()` sets the `activePlans()`
            // and stopper modifies that. instead of being `stopper` the one who sets it
            // stopper is responsible for filtering, not for making them available
            stopper(plans());
        }
    };
});
