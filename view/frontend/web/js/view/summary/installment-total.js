/**
 * Copyright © 2026 Line. All rights reserved.
 */
define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'Magento_Catalog/js/price-utils',
    'Line_Payment/js/model/promotions',
    'mage/translate'
], function (AbstractTotal, quote, checkoutTotals, priceUtils, promotionsModel, $t) {
    'use strict';

    return AbstractTotal.extend({
        defaults: {
            template: 'Line_Payment/summary/installment-total',
            title: 'Installment surcharge'
        },

        /**
         * Computes only the surcharge amount: grandTotal * (rate - 1).
         * Returns null when no plan is selected or rate is 1.0 (no surcharge).
         *
         * @returns {Number|null}
         */
        getInstallmentTotal: function () {
            var plan   = promotionsModel.selectedPlan(),
                totals = checkoutTotals.totals();

            if (!plan || !totals) {
                return null;
            }

            var rate = parseFloat(plan.rate);

            if (!rate || rate <= 1) {
                return null;
            }

            return parseFloat(totals.grand_total) * (rate - 1);
        },

        /**
         * Returns the dynamic row title including installment quantity and surcharge percentage.
         * Example: "Installment surcharge 3 payments 15%"
         *
         * @returns {String}
         */
        getTitle: function () {
            var plan = promotionsModel.selectedPlan();

            if (!plan || parseFloat(plan.rate) <= 1) {
                return $t('Installment surcharge');
            }

            var qty       = parseInt(plan.value, 10),
                surcharge = (parseFloat(plan.rate) - 1) * 100,
                formatedSurcharge = (Math.trunc(Number(surcharge.toFixed(8)) * 100) / 100).toFixed(2);

            return $t('Installment surcharge %1 payments %2%').replace('%1', qty).replace('%2', formatedSurcharge);
        },

        /**
         * @inheritdoc
         */
        isDisplayed: function () {
            return this.isFullMode() && this.getInstallmentTotal() !== null;
        },

        /**
         * Returns the formatted surcharge amount.
         *
         * @returns {String}
         */
        getValue: function () {
            var val = this.getInstallmentTotal();

            if (val === null) {
                return '';
            }

            return priceUtils.formatPrice(val.toFixed(2), quote.getPriceFormat());
        }
    });
});
