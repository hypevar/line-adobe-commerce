/**
 * Copyright © 2023 Line. All rights reserved.
 */

/**
 *
 */
define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push({
        type: 'linepayment',
        component: 'Line_Payment/js/view/payment/method-renderer/line-payment'
    });

    return Component.extend({});
});
