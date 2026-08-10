/**
 * Copyright © 2026 Line. All rights reserved.
 */
var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/summary/grand-total': {
                'Line_Payment/js/view/summary/grand-total-mixin': true
            },
            'Magento_Tax/js/view/checkout/summary/grand-total': {
                'Line_Payment/js/view/summary/grand-total-mixin': true
            }
        }
    }
};
