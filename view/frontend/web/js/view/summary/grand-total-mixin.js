/**
 * Copyright © 2026 Line. All rights reserved.
 */
define([
    'Line_Payment/js/model/promotions',
    'Magento_Checkout/js/model/totals'
], function (promotionsModel, checkoutTotals) {
    'use strict';

    return function (Component) {
        return Component.extend({

            /**
             * Returns the grand total formatted price, adjusted with the installment
             * surcharge rate when a plan with rate > 1 is selected.
             *
             * Overrides getValue() directly to be compatible with both:
             * - Magento_Checkout/js/view/summary/grand-total (uses getPureValue)
             * - Magento_Tax/js/view/checkout/summary/grand-total (uses getValue directly)
             *
             * @returns {String}
             */
            getValue: function () {
                var plan = promotionsModel.selectedPlan();

                if (!plan) {
                    return this._super();
                }

                var rate = parseFloat(plan.rate);

                if (!rate || rate <= 1) {
                    return this._super();
                }

                var totals = checkoutTotals.totals();

                if (!totals) {
                    return this._super();
                }

                var base = parseFloat(totals['grand_total']);

                return this.getFormattedPrice(base * rate);
            }
        });
    };
});
